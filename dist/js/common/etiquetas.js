const phpFilePathEtiquetas = "data_etiquetas.php";
const tblName = "articulospedidos";
function modalEtiquetas(params) {
  $("#modal-etiquetas").modal("show");
  $("#modal-etiquetas .row-etiquetas").html("");
    $("#print-wrapper").html("")
  let arr = [];
  if (!params){
    $("#tabla > tbody > .selected").each(function(e){
        const id_artpedido = $(this).attr("x-id-artpedido");
        arr.push(parseInt(id_artpedido));
    })
  }
  else{
    arr = params.productos
  }
  
  $.ajax({
    url: phpFilePathEtiquetas,
    type: "POST",
    data: {
      consulta: "get_etiquetas",
      productos: JSON.stringify(arr),
      table: params && params.tab ? params.tab+"_venta" : tblName,
    },
    success: function (x) {
      console.log(x)
      if (x.length) {
        let tipo = "";

          if (location.href.includes("mesadas")){
            tipo = "MESADA";
          }
          else if (location.href.includes("bandejas")){
            tipo = "BANDEJA";
          }
          else if (location.href.includes("esquejeras")){
            tipo = "ESQUEJERA";
          }
          else if (location.href.includes("venta")){
            tipo = "ELEM. VENTA";
          }

          const data = JSON.parse(x);
          if (data && data.length) {
            data.forEach(function (producto, index) {
              const etiqueta = generaEtiqueta(producto, index, tipo);
              const etiqueta2 = generaEtiquetaRaw(producto, index, tipo);
              
              $("#print-wrapper").append(etiqueta2)
              
              $("#modal-etiquetas .row-etiquetas").append(etiqueta);
              
                var qrcode = new QRCode(
                  document.getElementById("qr-code-" + index),
                  {
                    text: `${
                      producto.uniqid && producto.uniqid.length
                        ? producto.uniqid
                        : "asd"
                    }`,
                    width: 60, //default 128
                    height: 60,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.H,
                  }
                );
                console.log(qrcode)
                const dt = qrcode._oDrawing._elCanvas.toDataURL("image/png")
                $("#qr-code2-"+index).html(`<img class='qrcode2' src='${dt}'/>`);
              
            });
            //getSizeEtiquetas();
          }
        
      }
    },
  });
}
function setEtiquetaSize() {
  const w = $("#input-ancho").val().trim();
  const h = $("#input-alto").val().trim();

  const unit = $("#select-unidad-etiquetas option:selected").val();

  $(".container-etiqueta,.container-etiqueta2").css({
    width: w && w.length && parseInt(w) > 1 ? w + unit : "1px",
    height: h && h.length && parseInt(h) > 1 ? h + unit : "1px",
  });

}

function configPage(){
  const w = "400";
  const h = "150";

const wQR = "60";
  const hQR = "60";

  const unit = "px";

//   $(".container-etiqueta,.container-etiqueta2").css({
//     width: w && w.length && parseInt(w) > 1 ? w + unit : "1px",
//     height: h && h.length && parseInt(h) > 1 ? h + unit : "1px",
//   });

  document.getElementsByTagName('style')[0].innerHTML=`
  
  @media print {
    html {
        width: 100% !important;
        height:100% !important;
    }
    @page{
        size: 250px 60px !important;
        margin: 0 !important; 
    }
    #print-wrapper {
      width: 100% !important;
      height: 100% !important;
        margin: 0 !important;        
    }
    .qr-wrapper img {
      width: ${wQR && wQR.length && parseInt(wQR) > 1 ? wQR + unit : "1px"} !important;
      height: ${hQR && hQR.length && parseInt(hQR) > 1 ? hQR + unit : "1px"} !important;
    }

    .label-codigo {
      font-size:14px !important;
      font-weight:bold
    }
    .label-producto {
      font-size:11px !important;
    }
    .label-fecha {
      font-size:8px !important;
    }
          

    .container-etiqueta,.container-etiqueta2 {
        width: ${w && w.length && parseInt(w) > 1 ? (parseInt(w)+10) + unit : "1px"} !important;
        height: ${h && h.length && parseInt(h) > 1 ?(parseInt(h)+15) + unit : "1px"} !important;
    }


    .relative-container {
      padding-top:8px;
      padding-left:15px;
      position:relative;
      width:${w && w.length && parseInt(w) > 1 ? (parseInt(w)+30) + unit : "1px"} !important;
      height:100%;
      overflow:hidden;
    }
    
 }
  `; //some css goes here
}

function setQRSize() {
  const w = $("#input-ancho-qr").val().trim();
  const h = $("#input-alto-qr").val().trim();
  const unit = $("#select-unidad-etiquetas option:selected").val();

  $(".container-etiqueta,.container-etiqueta2")
    .find(".qr-wrapper img")
    .css({
      width: w && w.length && parseInt(w) > 1 ? w + unit : "1px",
      height: h && h.length && parseInt(h) > 1 ? h + unit : "1px",
    });
}

function setLogoSize() {
  const w = $("#input-ancho-logo").val().trim();
  const h = $("#input-alto-logo").val().trim();
  const unit = $("#select-unidad-etiquetas option:selected").val();

  $(".container-etiqueta,.container-etiqueta2")
    .find(".img-logo")
    .css({
      width: w && w.length && parseInt(w) > 1 ? w + unit : "1px",
      height: h && h.length && parseInt(h) > 1 ? h + unit : "1px",
    });
}

function setFontSize() {
  const fontSize = $("#input-font-size").val().trim();

  $(".label-etiqueta").css({
    fontSize:
      fontSize && fontSize.length && parseInt(fontSize) > 1
        ? fontSize + "px"
        : "10px",
  });
}

function generaEtiqueta(producto, index, tipo) {
  const { nombre_producto, id_artpedido, codigo,
    fecha_pedido,
   } = producto;

  const code = `
  <div class="col" id='etiq-${index}'>
    <div class="container-fluid mb-3 p-2 container-etiqueta" style="background: #ffffff;border: 1px solid #e1e2e2">
      <div class="d-flex flex-row">
        <div class="qr-wrapper" style='display:flex; align-items: center;' id="qr-code-${index}"></div>
        <div class="ml-3">
          <span class='label-codigo' style="font-size:18px;font-weight:bold;">${codigo}</span><br>
          <span class='label-producto' style='font-size:13px;'>${nombre_producto}</span><br> 
          <span class='label-fecha' style='font-size:10px;'>${fecha_pedido}</span>   
        </div>
      </div>  
    </div>
  </div>
  `;

  return code;
}

function generaEtiquetaRaw(producto, index, tipo) {
    const { nombre_producto, id_artpedido, codigo,
        fecha_pedido,
       } = producto;
  
    const code =
      `
      <div class="relative-container">
        <div class="container-etiqueta2" id='conta-${index}' style="background: #ffffff;overflow:hidden;">
          <div class="d-flex flex-row">
              <div class="qr-wrapper" style='display:flex; align-items: center;' id="qr-code2-${index}"></div>
              <div class="ml-3">
              <span class='label-codigo' style="font-size:18px;font-weight:bold;">${codigo}</span><br>
              <span class='label-producto' style='font-size:13px;'>${nombre_producto}</span><br> 
              <span class='label-fecha' style='font-size:10px;'>${fecha_pedido}</span>   
              </div>
            </div>  
        </div>  
      </div>
      <div class="salto" style=“page-break-after: always;”></div>
  `;
  
    return code;
  }

function imprimirEtiquetas() {
  configPage()
  $(".ocultar").css({ display: "none" });
  $(".modal-backdrop").css({ display: "none" });

  $("#print-wrapper").css({ display: "block" });
  const titulo = document.title;
  document.title = "Etiquetas";
  //$("#print-wrapper").find(".salto").last().remove();
  setTimeout(() => {
    window.print();
    document.title = titulo;
    $(".ocultar").css({ display: "block" });
    $(".modal-backdrop").css({ display: "block" });
    $("#print-wrapper").css({ display: "none" });
    //$("#print-wrapper").html("");
  }, 500);
}

function guardarSizeEtiquetas() {
  const wEtiqueta = $("#input-ancho").val().trim();
  const hEtiqueta = $("#input-alto").val().trim();
  const wQR = $("#input-ancho-qr").val().trim();
  const hQR = $("#input-alto-qr").val().trim();
  const wLogo = $("#input-ancho-logo").val().trim();
  const hLogo = $("#input-alto-logo").val().trim();
  const fontSize = $("#input-font-size").val().trim();
  const unit = $("#select-unidad-etiquetas option:selected").val();

  if (
    !wEtiqueta ||
    !wEtiqueta.length ||
    !hEtiqueta ||
    !hEtiqueta.length ||
    !wQR ||
    !wQR.length ||
    !hQR ||
    !hQR.length ||
    !wLogo ||
    !wLogo.length ||
    !hLogo ||
    !hLogo.length ||
    !fontSize ||
    !fontSize.length ||
    !unit ||
    !unit.length
  ) {
    Swal.fire({
      heightAuto: false,
      title: "Debes completar todos los campos!",
      text: "",
      icon: "error",
    });
    return;
  }

  $("#btn-guardar-size").prop("disabled", true);

  $.ajax({
    url: phpFilePathEtiquetas,
    type: "POST",
    data: {
      consulta: "guardar_size_etiquetas",
      table: tblName,
      wEtiqueta: wEtiqueta,
      hEtiqueta: hEtiqueta,
      wQR: wQR,
      hQR: hQR,
      wLogo: wLogo,
      hLogo: hLogo,
      fontSize: fontSize,
      unit: unit,
    },
    success: function (x) {
      console.log(x);
      if (x.includes("success")) {
        Swal.fire({
          heightAuto: false,
          title: "Guardaste los Cambios correctamente!",
          icon: "success",
        });
      } else {
        Swal.fire({
          heightAuto: false,
          title: "Ocurrió un error al guardar los Cambios",
          text: x,
          icon: "error",
        });
      }
      $("#btn-guardar-size").prop("disabled", false);
    },
    error: function (jqXHR, estado, error) {
      Swal.fire({
        heightAuto: false,
        title: "Ocurrió un error al guardar los Cambios",
        text: error,
        icon: "error",
      });
      $("#btn-guardar-size").prop("disabled", false);
    },
  });
}

function getSizeEtiquetas(){
    $.ajax({
        url: phpFilePathEtiquetas,
        type: "POST",
        data: {
          consulta: "get_size_etiquetas",
          table: tblName,
        },
        success: function (x) {
          console.log(x);
          if (x.length){
            try {
                const data = JSON.parse(x);
                const { wEtiqueta, hEtiqueta, wQR, hQR, wLogo, hLogo, fontSize, unit} = data;
                $("#input-ancho").val(wEtiqueta)
                $("#input-alto").val(hEtiqueta)
                $("#input-ancho-qr").val(wQR)
                $("#input-alto-qr").val(hQR)
                $("#input-ancho-logo").val(wLogo)
                $("#input-alto-logo").val(hLogo)
                $("#input-font-size").val(fontSize)
                $("#select-unidad-etiquetas").val(unit);
                setEtiquetaSize()
                setQRSize()
                setLogoSize();
                setFontSize()
            } catch (error) {
                $("#input-ancho").val(300)
                $("#input-alto").val(300)
                $("#input-ancho-qr").val(200)
                $("#input-alto-qr").val(200)
                $("#input-ancho-logo").val(200)
                $("#input-alto-logo").val(200)
                $("#input-font-size").val(20)
                $("#select-unidad-etiquetas").val("px");
                setEtiquetaSize()
                setLogoSize()
                setQRSize()
                setFontSize()
            }
          }
        },
    });
}



function generarUniqid(){
    $.ajax({
        url: phpFilePathEtiquetas,
        type: "POST",
        data: {
          consulta: "generar_uniqid",
        },
        success: function (x) {
          console.log(x);
          if (x.includes("success")) {
            swal("GENERASTE LA ID ", "", "success");
          } 
        },
        error: function (jqXHR, estado, error) {
          
        },
      });
}