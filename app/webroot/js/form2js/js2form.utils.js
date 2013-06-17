
var object2array = (function()
{
    
    var _subArrayRegexp = /^\[\d+?\]/,
    _subObjectRegexp = /^[a-zA-Z_][a-zA-Z_0-9]+/,
    _arrayItemRegexp = /\[[0-9]+?\]$/,
    _lastIndexedArrayRegexp = /(.*)(\[)([0-9]*)(\])$/,
    _arrayOfArraysRegexp = /\[([0-9]+)\]\[([0-9]+)\]/g,
    _inputOrTextareaRegexp = /INPUT|TEXTAREA/i;
    
    
    function object2array(obj, lvl)
    {
        var result = [], i, name;
        
        if (arguments.length == 1) lvl = 0;
        
        if (obj == null)
        {
            result = [{ name: "", value: null }];
        }
        else if (typeof obj == 'string' || typeof obj == 'number' || typeof obj == 'date' || typeof obj == 'boolean')
        {
            result = [
                { name: "", value : obj }
            ];
        }
        else if (obj instanceof Array)
        {
            for (i = 0; i < obj.length; i++)
            {
                name = "[" + i + "]";
                result = result.concat(getSubValues(obj[i], name, lvl + 1));
            }
        }
        else
        {
            for (i in obj)
            {
                name = i;
                result = result.concat(getSubValues(obj[i], name, lvl + 1));
            }
        }
        
        return result;
    }
    
    function getSubValues(subObj, name, lvl)
    {
        var itemName;
        var result = [], tempResult = object2array(subObj, lvl + 1), i, tempItem;
        
        for (i = 0; i < tempResult.length; i++)
        {
            itemName = name;
            if (_subArrayRegexp.test(tempResult[i].name))
            {
                itemName += tempResult[i].name;
            }
            else if (_subObjectRegexp.test(tempResult[i].name))
            {
                itemName += '.' + tempResult[i].name;
            }
            
            tempItem = { name: itemName, value: tempResult[i].value };
            result.push(tempItem);
        }
        
        return result;
    }
    
    return object2array;
    
})();