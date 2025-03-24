const phpFile = "data_atributos.php";

function modalEditarValor(id, nombre, precio) {

}
function modalAtributos() {
    $("#modal-atributos input").val("");
    getTableAtributos();
    $("#modal-atributos").modal("show");

    $("#modal-atributos input").focus();
}

function guardarAtributo() {
    const nombre = $("#input-nombre-atributo").val().trim().replace(/\s/g, " ");
    if (!nombre || !nombre.length) {
        swal("Ingresa el Nombre del Tipo de Atributo", "", "error");
        return;
    }

    $("#modal-atributos .btn-guardar").prop("disabled", true);
    $.ajax({
        beforeSend: function () { },
        url: phpFile,
        type: "POST",
        data: {
            consulta: "guardar_atributo",
            nombre: nombre,
        },
        success: function (x) {
            if (x.trim() == "success") {
                $("#modal-atributos input").val("");
                $("#modal-atributos input").focus();
                getAtributosSelect();
                getTableAtributos();
            } else {
                swal("Ocurrió un error al guardar el Atributo", x, "error");
            }
            $("#modal-atributos .btn-guardar").prop("disabled", false);
        },
        error: function (jqXHR, estado, error) { },
    });
}

function getTableAtributos() {
    $("#table-crud > tbody").html("");
    $.ajax({
        url: phpFile,
        type: "POST",
        data: {
            consulta: "get_table_atributos",
        },
        success: function (x) {
            console.log(x);
            $("#table-crud > tbody").html(x);
            setInputInt(".input-int");
        },
    });
}


function guardarValorAtributo(id, obj) {
    // 获取属性值
    const valor = $(obj)
        .parent()
        .find("input")
        .first()
        .val()
        .trim()
        .replace(/\s/g, " ");
    const precioExtra = $(obj)
        .parent()
        .find(".input-int")
        .val()
        .trim()
        .replace(/\s/g, "");
    if (!valor || !valor.length) {
        swal("Ingresa el Valor del Atributo", "", "error");
        return;
    }
    $(obj).prop("disabled", true);
    $.ajax({
        beforeSend: function () { },
        url: phpFile,
        type: "POST",
        data: {
            consulta: "guardar_valor_atributo",
            valor: valor.toUpperCase(),
            precioExtra:
                precioExtra && precioExtra.length && parseInt(precioExtra) > 0
                    ? precioExtra
                    : null,
            id: id,
        },
        success: function (x) {
            if (x.trim() == "success") {
                $(obj).parent().find("input").val("");
                $(obj).parent().find("input").first().focus();
                getTableAtributos();
            } else {
                swal("Ocurrió un error al guardar el Valor del Atributo", x, "error");
            }
            $(obj).prop("disabled", false);
        },
        error: function (jqXHR, estado, error) {
            $(obj).prop("disabled", false);
        },
    });
}

function eliminarValorAtributo(id, obj, nombre) {
    $(obj).prop("disabled", true);
    swal(`Estás seguro/a de eliminar el Valor ${nombre}?`, "", {
        icon: "warning",
        buttons: {
            cancel: "Cancelar",
            catch: {
                text: "ELIMINAR",
                value: "catch",
            },
        },
    }).then((value) => {
        switch (value) {
            case "catch":
                $.ajax({
                    beforeSend: function () { },
                    url: phpFile,
                    type: "POST",
                    data: {
                        consulta: "eliminar_valor_atributo",
                        id: id,
                    },
                    success: function (x) {
                        if (x.trim() == "success") {
                            getTableAtributos();
                        } else {
                            swal("Ocurrió un error al eliminar el Valor", x, "error");
                            $(obj).prop("disabled", false);
                        }
                    },
                    error: function (jqXHR, estado, error) { },
                });

                break;

            default:
                break;
        }
    });
}

function eliminarAtributo(id, obj, nombre) {
    $(obj).prop("disabled", true);
    swal(`Estás seguro/a de eliminar el Atributo ${nombre}?`, "", {
        icon: "warning",
        buttons: {
            cancel: "Cancelar",
            catch: {
                text: "ELIMINAR",
                value: "catch",
            },
        },
    }).then((value) => {
        switch (value) {
            case "catch":
                $.ajax({
                    beforeSend: function () { },
                    url: phpFile,
                    type: "POST",
                    data: {
                        consulta: "eliminar_atributo",
                        id: id,
                    },
                    success: function (x) {
                        if (x.trim() == "success") {
                            getTableAtributos();
                        } else {
                            swal("Ocurrió un error al eliminar el Atributo", x, "error");
                            $(obj).prop("disabled", false);
                        }
                    },
                    error: function (jqXHR, estado, error) {
                        $(obj).prop("disabled", false);
                    },
                });

                break;

            default:
                break;
        }
    });
}

function getAtributosSelect() {
    let atributosSelect = "";
    $.ajax({
        beforeSend: function () {
            atributosSelect = null;
        },
        url: phpFile,
        type: "POST",
        data: { consulta: "get_atributos_select" },
        success: function (x) {
            console.log(x);
            if (x.includes("option")) {
                atributosSelect = x.trim();
                $("#select_filtro_atributos").html(x).selectpicker("refresh")
            }
        },
        error: function (jqXHR, estado, error) { },
    });
}

function setInputInt(obj) {
    $(obj).on("propertychange input", function (e) {
        this.value = this.value.replace(/\D/g, "");
    });
}

function setInputDecimal(obj) {
    $(obj)
        .on("keypress", function (evt) {
            let $txtBox = $(this);
            let charCode = evt.which ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46)
                return false;
            else {
                let len = $txtBox.val().length;
                let index = $txtBox.val().indexOf(".");
                if (index > 0 && charCode == 46) {
                    return false;
                }
                if (index > 0) {
                    let charAfterdot = len + 1 - index;
                    if (charAfterdot > 3) {
                        return false;
                    }
                }
            }
            return $txtBox; //for chaining
        })
        .on("paste", function (e) {
            return false;
        });
}


function getAtributosVariedad(id) {
    $("#table-atributos > tbody").html("");

    $.ajax({
        url: phpFile,
        type: "POST",
        data: {
            consulta: "get_atributos_variedad",
            id: id ?? null
        },
        success: function (x) {
            console.log(x);
            if (x.length) {
                try {
                    const data = JSON.parse(x);
                    data.forEach(function (e) {
                        const { id, nombre, valores } = e;

                        let input = "";
                        if (valores && valores.length) {
                            input = `
                            <select class="selectpicker" data-dropup-auto="false"
                            title="Valor" data-container="body" data-size="5" data-style="btn-info" data-width="350px">`;
                                        valores.forEach(function (e) {
                                            input += `
                                <option ${e.selected ? "selected" : ""} value='${e.id}'>${e.valor}</option>
                            `;
                                        });

                                        input += `</select>
                            `;
                        }

                        $("#table-atributos > tbody").append(`
                            <tr x-id='${id}'>
                            <td>${nombre}</td>
                            <td>
                                ${input}
                            </td>
                            </tr>
                        `);
              

                    });

                    setInputDecimal(".input-decimal");
                    setInputInt(".input-int");
                    $("#table-atributos .selectpicker").selectpicker("refresh");
                } catch (error) {
                    console.error(error);
                }
            }
        },
    });

    $(".tr-add-row").first().before(`
        <tr>
          <td>
            <input type='search' autocomplete="off" class="form-control" maxlength="20"/>
          </td>
          <td>
            <select class="selectpicker" data-container="body" data-dropup-auto="false"
            title="Tipo" data-style="btn-info">
              <!--
              <option value="VARCHAR">TEXTO CORTO</option>
              <option value="TEXT">TEXTO LARGO / DESCRIPCION</option>
              <option value="INT">NÚMERO ENTERO</option>
              <option value="DECIMAL">NÚMERO DECIMAL</option>
              
              <option value="DATE">FECHA</option>
              <option value="PRODUCTO">REF. OTRO PRODUCTO</option>
              <option value="COLOR">COLOR SELECCIONABLE</option>
              -->
            </select>
          </td>
          <td class="text-center">
            <button onclick="$(this).parent().parent().remove()" class="btn btn-secondary fa fa-trash btn-sm"></button>
          </td>
        </tr>
      `);

    $("#table-tipos-atributo .selectpicker").selectpicker("refresh");
}