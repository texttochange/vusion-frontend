function createTable(selector, options) {
    $(selector).handsontable(options);
}

function tableRenderer(instance, td, row, col, prop, value, cellProperties) {
    Handsontable.TextCell.renderer.apply(this, arguments);
    if (row === 0) {
        td.className = "key";
        td.setAttribute("title", "This is a readonly key.")
        cellProperties.readOnly = true;
    } else if (col < cellProperties.lastColKey){
        td.className = "key";
        td.setAttribute("title", "This is a readonly key.")
        cellProperties.readOnly = true;
    } else {
        td.setAttribute("title", "This value is editable and can be used in message.");
        /*if ('className' in cellProperties && cellProperties!="") {
            td.className = cellProperties.className; 
        }
        if ($(td).attr('class') == 'cell-failure') {
            cellProperties.className = td.className;
        }*/
    }
}

function keyRenderer(instance, td, row, col, prop, value, cellProperties) {
    Handsontable.TextCell.renderer.apply(this, arguments);
    td.className = "key";
    td.setAttribute("title", "This is a readonly key.")
    cellProperties.readOnly = true;
}

function valueRenderer(instance, td, row, col, prop, value, cellProperties) {
    Handsontable.TextCell.renderer.apply(this, arguments);
    if (cellProperties.valid) {
        td.setAttribute("title", "This value is editable and can be used in message.");
    } else {
        td.setAttribute("title", cellProperties.validationError);
    }
}

function saveTable() {
    //Remove validation errors
    $(".error").removeClass("error");
    $(".error-message").remove();
    //Deactivate save button to avoid double click while saving
    disableSaveButtons();
    //Send form
    formData = form2js('content-variable-table', '.', true);
    if (!("ContentVariableTable" in formData)) {
        formData.ContentVariableTable = [];
    }
    tableData = $("#columns").handsontable("getData");
    vusionTable = fromHandsontableToVusionTable(tableData);
    formData.ContentVariableTable['columns'] = vusionTable;
    var data= JSON.stringify(formData, null, '\t');
    $.ajax({
            url: location.href+'.json',
            type: 'POST',
            data: data,
            contentType: 'application/json; charset=utf-8',
            dataType: 'json',
            success: function (data) {
                if (data.status === "ok") {
                    $("#flashMessage").show().attr('class', 'message success').text(localized_messages.table_saved+" "+localized_messages.wait_redirection);
                    if (location.href.indexOf("editTable/")<0) {
                         window.location.replace("indexTable")
                    } else {
                        window.location.replace("../indexTable");
                    }
                } else if (data.status === "fail") {
                    showValidationError($("#columns"), data.reason);
                    reactivateSaveButtons();
                }
            },
            error: saveAjaxError,
    })
}

function showValidationError(tableElement, validationErrors) {
    var table = tableElement.data('handsontable');
    if ("name" in validationErrors) {
        $('#content-variable-table [name$=".name"]').parent().addClass("error").append('<div class="error-message">'+validationErrors.name[0]+'</div>');
    }
    if ("columns" in validationErrors) {
        for (var colIndex in validationErrors.columns[0]) {
            if ((!$.isArray(validationErrors.columns[0]) && (typeof validationErrors.columns[0] === 'string'))) {
                columnsErrorMessage = validationErrors.columns[0];                
            } else {
                if ("header" in validationErrors.columns[0][colIndex]) {
                    rowIndex = 0;
                    message = validationErrors.columns[0][colIndex].header[0];
                    $(table.getCell(rowIndex, colIndex)).addClass("error").attr('title', message);
                }
                if ("values" in validationErrors.columns[0][colIndex]) { 
                    for (var rowIndex in validationErrors.columns[0][colIndex].values[0]) {
                        message = validationErrors.columns[0][colIndex].values[0][rowIndex];
                        $(table.getCell(parseFloat(rowIndex)+1, colIndex)).addClass("error").attr('title', message);
                    }
                }
                columnsErrorMessage = "Please the value(s) of the red cells. To learn more about the issue, move over with your mouse.";
            }
            if (!tableElement.parent().hasClass("error")) {
                tableElement.parent().addClass("error").append('<div class="error-message">'+columnsErrorMessage+'</div>');
            }  
        }   
    }
}

function fromHandsontableToVusionTable(table) {
    var vusionTable = [];
    var nbrRows = table.length,
        nbrCols = table[0].length;
    for (var i=0; i<nbrCols; i++) {
        var column = {};
        column.header = table[0][i];
        column.values = [];
        for (var j=0; j<nbrRows-1; j++) {
            column.values[j] = table[j+1][i];
        }
        vusionTable[i] = column;
    }
    return vusionTable;
}

function fromVusionToHandsontableData(table) {
    table = $.parseJSON(table);
    var handsontable = [];
    var nbrCols = table.length,
        nbrRows = table[0].values.length + 1;
    for (var i=0; i<nbrRows; i++) {
        handsontable.push([]);
        if (i==0) {
            for (var j=0; j<nbrCols; j++) {
                handsontable[0].push(table[j]['header']);
            }
        } else {
            for (var j=0; j<nbrCols; j++) {
                handsontable[i].push(table[j].values[i-1]);
            }
        }
    }
    return handsontable;
}

function fromVusionToHandsontableColumns(table, defaultRegex) {
    var columns = []
    table = $.parseJSON(table);
    var nbrCols = table.length;
    for (var i=0; i<nbrCols; i++) {
        if (table[i]['type'] === 'key') {
            columns.push({});
        } else {
            columns.push({'validator': defaultRegex, 'allowInvalid': true});
        }
    }
    return columns;
}


function saveContentVariableValue() {

}