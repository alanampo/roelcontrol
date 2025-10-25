/**
 * Módulo de Compresión de Imágenes
 *
 * Optimiza imágenes antes de subirlas al servidor:
 * - Redimensiona a máximo 1200px de ancho
 * - Comprime a 80% de calidad JPEG
 * - Convierte PNG/WEBP a JPEG (más ligero)
 * - Estima tamaño antes y después
 * - Mantiene orientación EXIF
 */

const ImageCompressor = {
  // Configuración por defecto
  config: {
    maxWidth: 1200,
    maxHeight: 1200,
    quality: 0.8,
    outputFormat: 'image/jpeg'
  },

  /**
   * Comprime un archivo de imagen
   * @param {File} file - Archivo de imagen a comprimir
   * @param {Object} options - Opciones de compresión (opcional)
   * @returns {Promise<Object>} - Objeto con archivo comprimido y metadatos
   */
  compress: function(file, options = {}) {
    const settings = Object.assign({}, this.config, options);

    return new Promise((resolve, reject) => {
      // Validar que sea imagen
      if (!file.type.match(/image.*/)) {
        reject(new Error('El archivo no es una imagen válida'));
        return;
      }

      const reader = new FileReader();

      reader.onload = (e) => {
        const img = new Image();

        img.onload = () => {
          try {
            // Calcular nuevas dimensiones manteniendo aspecto
            let width = img.width;
            let height = img.height;

            if (width > settings.maxWidth || height > settings.maxHeight) {
              const ratio = Math.min(
                settings.maxWidth / width,
                settings.maxHeight / height
              );
              width = Math.floor(width * ratio);
              height = Math.floor(height * ratio);
            }

            // Crear canvas y dibujar imagen redimensionada
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const ctx = canvas.getContext('2d');

            // Fondo blanco para transparencias (JPEG no soporta transparencia)
            ctx.fillStyle = '#FFFFFF';
            ctx.fillRect(0, 0, width, height);

            // Dibujar imagen
            ctx.drawImage(img, 0, 0, width, height);

            // Convertir a blob comprimido
            canvas.toBlob((blob) => {
              if (!blob) {
                reject(new Error('Error al comprimir la imagen'));
                return;
              }

              // Crear nuevo archivo con nombre único
              const timestamp = Date.now();
              const extension = 'jpg';
              const originalName = file.name.replace(/\.[^/.]+$/, '');
              const newFileName = `${originalName}_${timestamp}.${extension}`;

              const compressedFile = new File([blob], newFileName, {
                type: settings.outputFormat,
                lastModified: Date.now()
              });

              // Calcular estadísticas
              const originalSizeKB = Math.round(file.size / 1024);
              const compressedSizeKB = Math.round(compressedFile.size / 1024);
              const reductionPercent = Math.round(
                ((file.size - compressedFile.size) / file.size) * 100
              );

              resolve({
                file: compressedFile,
                original: {
                  name: file.name,
                  size: file.size,
                  sizeKB: originalSizeKB,
                  width: img.width,
                  height: img.height
                },
                compressed: {
                  name: newFileName,
                  size: compressedFile.size,
                  sizeKB: compressedSizeKB,
                  width: width,
                  height: height
                },
                stats: {
                  reduction: reductionPercent,
                  ratio: (file.size / compressedFile.size).toFixed(2)
                }
              });

            }, settings.outputFormat, settings.quality);

          } catch (error) {
            reject(error);
          }
        };

        img.onerror = () => {
          reject(new Error('Error al cargar la imagen'));
        };

        img.src = e.target.result;
      };

      reader.onerror = () => {
        reject(new Error('Error al leer el archivo'));
      };

      reader.readAsDataURL(file);
    });
  },

  /**
   * Comprime múltiples imágenes
   * @param {FileList|Array} files - Lista de archivos
   * @param {Function} progressCallback - Callback de progreso (opcional)
   * @returns {Promise<Array>} - Array con resultados de compresión
   */
  compressMultiple: function(files, progressCallback) {
    const filesArray = Array.from(files);
    const promises = filesArray.map((file, index) => {
      return this.compress(file).then(result => {
        if (progressCallback) {
          progressCallback(index + 1, filesArray.length, result);
        }
        return result;
      });
    });

    return Promise.all(promises);
  },

  /**
   * Crea una preview de la imagen comprimida
   * @param {File} file - Archivo comprimido
   * @returns {Promise<String>} - Data URL de la imagen
   */
  createPreview: function(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.onload = (e) => resolve(e.target.result);
      reader.onerror = reject;
      reader.readAsDataURL(file);
    });
  },

  /**
   * Valida tamaño de archivo
   * @param {File} file - Archivo a validar
   * @param {Number} maxSizeMB - Tamaño máximo en MB
   * @returns {Boolean}
   */
  validateSize: function(file, maxSizeMB = 5) {
    return file.size <= maxSizeMB * 1024 * 1024;
  },

  /**
   * Formatea bytes a texto legible
   * @param {Number} bytes - Tamaño en bytes
   * @returns {String}
   */
  formatBytes: function(bytes) {
    if (bytes === 0) return '0 Bytes';

    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
  }
};

// Exportar para uso global
window.ImageCompressor = ImageCompressor;
