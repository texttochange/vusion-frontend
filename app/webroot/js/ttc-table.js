function createTable(selector, options) {
    $(selector).handsontable(options);
}

function getKeysFromCellPosition(handsontable, row, col) {
    var i = 0,
        keys = [];
    while (handsontable.getCell(row, i).className === "key") {
        keys.push(handsontable.getDataAtCell(row, i));
        i++;
    }
    keys.push(handsontable.getDataAtCell(0, col));
    return keys;
}

function fromKeysToCustomizedContent(keys) {
    contentVariableList = ['contentVariable'].concat(keys);
    contentVariable = contentVariableList.join('.');
    return '['+contentVariable+']';
}

function keyRenderer(instance, td, row, col, prop, value, cellProperties) {
    Handsontable.TextCell.renderer.apply(this, arguments);
    td.className = "key";
    td.setAttribute("title", "This is a readonly key.")
    cellProperties.readOnly = true;
}

function valueRenderer(instance, td, row, col, prop, value, cellProperties) {
    Handsontable.TextCell.renderer.apply(this, arguments);
}

function valueReadOnlyRenderer(instance, td, row, col, prop, value, cellProperties) {
    Handsontable.TextCell.renderer.apply(this, arguments);
    cellProperties.readOnly = true;
}

// the title can only be define after the all table has been render.
// otherwise we cannot construct the customized key. 
function setTableTitles(isForced) {
    if (!isForced) {
        return;
    }
    for (var r=0; r<this.countRows(); r++) {
        for (var c=0; c<this.countCols(); c++) {
            cellProperties = this.getCellMeta(r, c);
            if (cellProperties.renderer != valueRenderer) {
                continue;
            }
            if ('valid' in cellProperties && (!cellProperties.valid)) {
                title = cellProperties.validationError; 
            } else {
                customizedContent = fromKeysToCustomizedContent(getKeysFromCellPosition(this, r, c));
                title = localized_messages.content_variable_table_value + customizedContent;
            }
            cell = this.getCell(r,c);
            cell.setAttribute("title", title);
        }
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
                if (data['status'] === "ok") {
                    $("#flashMessage").show().attr('class', 'message success').text(localized_messages.table_saved+" "+localized_messages.wait_redirection);
                    if (location.href.indexOf("editTable/")<0) {
                         window.location.replace("indexTable")
                    } else {
                        window.location.replace("../indexTable");
                    }
                } else if (data['status'] === "fail") {
                    showValidationError($("#columns"), data['validation-errors']);
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

function saveValueCallback(data) {
    var cell = $("#"+this.callbackData.table).handsontable("getCell", this.callbackData.change[0], this.callbackData.change[1]);
    var cellClass = "htInvalid";
    if (data.status == "ok") {
        cellClass = "cell-success";
        setTimeout(function(){
                $(cell).removeClass();
        },2000);
        var cellProperties = $("#"+this.callbackData.table).handsontable("getCellMeta", this.callbackData.change[0], this.callbackData.change[1]);
        cellProperties.valid = true;
    } else {
        var cellProperties = $("#"+this.callbackData.table).handsontable("getCellMeta", this.callbackData.change[0], this.callbackData.change[1]);
        cellProperties.valid = false;
        cellProperties.validationError = data['validation-errors'];
        cell.setAttribute('title', cellProperties.validationError);
    }
    cell.className = cellClass;
}