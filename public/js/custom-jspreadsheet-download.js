/**
 * Official jExcel Plugins
 *
 * Author: Paul Hodel <paul.hodel@gmail.com>
 * Website: https://jspreadsheet.com
 * Description: Create amazing web based spreadsheets.
 *
 * This software is free. Visit: https://jspreadsheet.com/v7
 */

jexcel.download = (function (el, projectName) {
    var data = '';

    var plugin = {};
    var worksheets = [];
    var styles = {};
    var styleIndex = 1;

    /**
     * Helpers to deal with the export to jexcel to excel
     */
    var helpers = function () {
        var o = {};
        o.getData = function () {
            // Create XML cell
            var get = function (o) {
                if (!o.data && !o.comments) {
                    return '<Cell/>';
                } else {
                    return `<Cell${o.styleId}${o.formula}><Data ss:Type="${o.type}">${o.data}</Data>${o.comments}</Cell>`;
                }
            }

            // Containers
            let reportTitleRow = '<Row><Cell/><Cell/><Cell/><Cell/><Cell/><Cell ss:StyleID="1"><Data ss:Type="String">Estimate Spreadsheet Report</Data></Cell><Cell/><Cell/><Cell/><Cell/><Cell/></Row>';
            let jobNameRow = `<Row><Cell/><Cell/><Cell/><Cell/><Cell/><Cell ss:StyleID="1"><Data ss:Type="String">${projectName}</Data></Cell><Cell/><Cell/><Cell/><Cell/><Cell/></Row>`;
            let emptyRow = `<Row><Cell/><Cell/><Cell/><Cell/><Cell/><Cell/><Cell/><Cell/><Cell/><Cell/><Cell/></Row>`;
            let columnHeaderRow = `<Row>
                                        <Cell><Data ss:Type="String">Cost Group number</Data></Cell>
                                        <Cell><Data ss:Type="String">Cost Group description</Data></Cell>
                                        <Cell><Data ss:Type="String">Item number</Data></Cell>
                                        <Cell><Data ss:Type="String">Item description</Data></Cell>
                                        <Cell><Data ss:Type="String">Takeoff Quantity</Data></Cell>
                                        <Cell><Data ss:Type="String">Takeoff UOM</Data></Cell>
                                        <Cell><Data ss:Type="String">Labor Conversion to Order UOM</Data></Cell>
                                        <Cell><Data ss:Type="String">Labor Order Quantity</Data></Cell>
                                        <Cell><Data ss:Type="String">Labor Order UOM</Data></Cell>
                                        <Cell><Data ss:Type="String">Labor Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Labor Total</Data></Cell>
                                        <Cell><Data ss:Type="String">Material Conversion to Order UOM</Data></Cell>
                                        <Cell><Data ss:Type="String">Material Order Quantity</Data></Cell>
                                        <Cell><Data ss:Type="String">Material Order UOM</Data></Cell>
                                        <Cell><Data ss:Type="String">Material Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Material Total</Data></Cell>
                                        <Cell><Data ss:Type="String">Subcontract Conversion to Order UOM</Data></Cell>
                                        <Cell><Data ss:Type="String">Subcontract Order Quantity</Data></Cell>
                                        <Cell><Data ss:Type="String">Subcontract Order UOM</Data></Cell>
                                        <Cell><Data ss:Type="String">Subcontract Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Subcontract Total</Data></Cell>
                                        <Cell><Data ss:Type="String">HD SKU</Data></Cell>
                                        <Cell><Data ss:Type="String">HD Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Lowes SKU</Data></Cell>
                                        <Cell><Data ss:Type="String">Lowes Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Whitecap SKU</Data></Cell>
                                        <Cell><Data ss:Type="String">Whitecap Price</Data></Cell>
                                        <Cell><Data ss:Type="String">BLS Number</Data></Cell>
                                        <Cell><Data ss:Type="String">BLS Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Grainger Number</Data></Cell>
                                        <Cell><Data ss:Type="String">Grainger Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Wire & Cable YW Number</Data></Cell>
                                        <Cell><Data ss:Type="String">Wire & Cable YW Price</Data></Cell>
                                        <Cell><Data ss:Type="String">Selected Vendor</Data></Cell>
                                        <Cell><Data ss:Type="String">Quote/Invoice item</Data></Cell>
                                        <Cell><Data ss:Type="String">Total Cost</Data></Cell>
                                        <Cell><Data ss:Type="String">Location</Data></Cell>
                                        <Cell><Data ss:Type="String">Notes</Data></Cell>
                                    </Row>`;

            var rows = [jobNameRow, reportTitleRow, emptyRow, columnHeaderRow];
            var cols = [];

            // Get data
            this.data = this.instance.data(null, false, false);
            for (var j = 0; j < this.data.length; j++) {
                cols = [];
                // starting from 2 to remove first two columns
                for (var i = 2; i < this.data[j].length; i++) {
                    // Create cells
                    var o = { styleId: '', type: '', data: '', formula: '' };
                    // Column name
                    o.columnName = jexcel.helpers.getColumnNameFromCoords(i, j);
                    var columnIndex = j + 1;
                    if (o.columnName !== 'N' + columnIndex && o.columnName !== 'O' + columnIndex && o.columnName !== 'P' + columnIndex &&
                        o.columnName !== 'V' + columnIndex && o.columnName !== 'W' + columnIndex && o.columnName !== 'X' + columnIndex &&
                        o.columnName !== 'AD' + columnIndex && o.columnName !== 'AE' + columnIndex && o.columnName !== 'AF' + columnIndex &&
                        o.columnName !== 'AG' + columnIndex && o.columnName !== 'AH' + columnIndex && o.columnName !== 'AI' + columnIndex &&
                        o.columnName !== 'AJ' + columnIndex && o.columnName !== 'AK' + columnIndex && o.columnName !== 'AL' + columnIndex &&
                        o.columnName !== 'AM' + columnIndex && o.columnName !== 'AN' + columnIndex) {
                        // Set data;
                        o.data = this.data[j][i];
                        // Get comments
                        o.comments = this.getComments(o.columnName);
                        // Style
                        o.styleId = this.getStyle(o.columnName, i, j);
                        // Type
                        if (this.instance.options.columns[i].type == 'calendar') {
                            o.styleId = ' ss:StyleID="calendar"';
                            o.type = 'DateTime';
                        } else if (this.instance.options.columns[i].type == 'checkbox' || this.instance.options.columns[i].type == 'radio') {
                            o.type = 'String'
                        } else if (jSuites.isNumeric(o.data)) {
                            o.type = 'Number';
                        } else {
                            o.type = 'String';
                        }
                        // Formulas
                        if (('' + o.data).substr(0, 1) == '=') {
                            o.formula = ' ss:Formula="' + this.getFormula(o.data) + '"';
                            o.data = this.instance.records[j][i].element ? this.instance.records[j][i].element.innerHTML : '';
                        }

                        cols.push(get(o));
                    }
                }

                rows.push('<Row>' + cols.join('') + '</Row>');
            }

            // Width
            var width = '';
            for (var i = 2; i < this.instance.headers.length; i++) {
                width += '<Column ss:AutoFitWidth="0" ss:Width="' + parseInt(this.instance.options.columns[i].width) + '"/>';
            }

            // Formatted data
            this.data = `<Worksheet ss:Name="${this.name}"><Table>${width}${rows.join('')}</Table></Worksheet>`;
        }

        o.getFormula = function (formula) {
            // Valid token
            var token = new RegExp(/([A-Z]+[0-9]+)(:[A-Z]+[0-9]+)?/i);
            // Tokenize formula
            var newFormula = jexcel.helpers.tokenize(formula, true);
            // Update token
            var updateToken = function (v) {
                if (v.indexOf('!') == -1) {
                    var c = jexcel.helpers.getCoordsFromColumnName(v);
                    var t = '';
                } else {
                    var f = newFormula[i].split('!');
                    var c = jexcel.helpers.getCoordsFromColumnName(f[1]);
                    var t = f[0] + '!';
                }

                // var x1 = c[0] + 1;
                // var y1 = c[1] + 1;
                var x1 = c[0] - 1;
                var y1 = c[1] + 5;

                switch (c[0]) {
                    case 16: // mat con
                        x1 = c[0] - 4;
                        break;
                    case 17: // mat order qty
                        x1 = c[0] - 4;
                        break;
                    case 19: // mat price
                        x1 = c[0] - 4;
                        break;
                    case 20: // mat total
                        x1 = c[0] - 4;
                        break;
                    case 24: // sub con
                        x1 = c[0] - 7;
                        break;
                    case 25: // sub order qty
                        x1 = c[0] - 7;
                        break;
                    case 27: // sub price
                        x1 = c[0] - 7;
                        break;
                    case 28: // sub total
                        x1 = c[0] - 7;
                        break;
                    case 32:
                        x1 = c[0] - 10; // other con
                        break;
                    case 33: // other order qty
                        x1 = c[0] - 10;
                        break;
                    case 35:
                        x1 = c[0] - 10; // other price
                        break;
                    case 36: // other total
                        x1 = c[0] - 10;
                        break;
                    default:
                        break;
                }


                return t + 'R' + y1 + 'C' + x1;
            }

            for (var i = 0; i < formula.length; i++) {
                if (newFormula[i] && token.test(newFormula[i])) {
                    if (newFormula[i].indexOf(':') == -1) {
                        newFormula[i] = updateToken(newFormula[i]);
                    } else {
                        var t = newFormula[i].split(':');
                        newFormula[i] = updateToken(t[0]) + ':' + updateToken(t[1]);
                    }
                }
            }

            return newFormula.join('');
        }

        o.getComments = function (columnName) {
            var comments = '';
            if (comments = this.instance.getComments(columnName)) {
                return `<Comment><Data>${comments}</Data></Comment>`;
            } else {
                return '';
            }
        }

        o.getStyle = function (columnName, i, j) {
            if (this.instance.records[j][i].element) {
                var val = this.instance.records[j][i].element.getAttribute('style');
            } else {
                var val = this.instance.getStyle(columnName);
            }
            if (val) {
                if (!styles[val]) {
                    styles[val] = {
                        id: styleIndex++,
                        content: plugin.applyStyle(val),
                    }
                }
                return ' ss:StyleID="' + styles[val].id + '"';
            } else {
                return '';
            }
        }

        return o;
    }


    /**
     * Parse the CSS style
     */
    plugin.parseCSS = function (css) {
        var result = [];
        css = css.split(';');

        for (var i = 0; i < css.length; i++) {
            if (css[i].trim()) {
                var t = css[i].split(':');
                result[t[0].trim()] = t[1].trim();
            }
        }

        return result;
    }

    /**
     * Extract the border style
     */
    plugin.parseBorderCSS = function (css) {
        // Options
        var parsed = { 'size': null, 'color': null, 'style': null };

        // Explode
        css = css.toLowerCase().split(' ');

        for (var i = 0; i < css.length; i++) {
            if (css[i].indexOf('px') >= 0) {
                parsed.size = parseInt(css[i]);
            } else if (css[i].indexOf('#') >= 0) {
                parsed.color = plugin.rgbToHex(css[i]);
            } else if (css[i] == 'dashed') {
                parsed.style = 'dashed';
            } else if (css[i] == 'dotted') {
                parsed.style = 'dotted';
            } else if (css[i] == 'solid') {
                parsed.style = 'thin';
            }
        }

        return parsed;
    }

    /**
     * Apply the style onto excel format
     */
    plugin.applyStyle = function (css) {
        css = plugin.parseCSS(css);

        var style = [];
        var Font = [];

        if (css['font-style'] && css['font-style'] == 'italic') {
            Font.push('ss:Italic="1"');
        }

        if (css['font-weight']) {
            Font.push('ss:Bold="1"');
        }

        if (css['font-size']) {
            v = css['font-size'];
            if (!jSuites.isNumeric(v)) {
                var v = { 'x-small': 8, 'small': 10, 'medium': 12, 'large': 16, 'x-large': 20 };
                v = v[css['font-size']] ? v[css['font-size']] : 10;
            }
            v = parseInt(v);
            if (v) {
                Font.push('ss:Size="' + v + '"');
            }
        }

        if (css['color']) {
            var v = css['color'].toUpperCase().trim();
            Font.push('ss:Color="' + plugin.rgbToHex(v) + '"');
        }

        if (css['font-family']) {
            Font.push('ss:FontName="' + css['font-family'] + '"');
        }

        if (Font.length) {
            style.push('<Font ' + Font.join(' ') + '/>');
        }

        if (css['text-align']) {
            var v = css['text-align'].charAt(0).toUpperCase() + css['text-align'].slice(1);
            style.push('<Alignment ss:Horizontal="' + v + '" />');
        }

        var Borders = [];

        var getBorder = function (a, b, position) {
            var t = [];

            if (!a) {
                a = {};
            }
            if (!b) {
                b = {};
            }

            if (b.style || a.style) {
                var v = b.style || a.style;
                t.push('ss:borderStyle="' + v + '"');
            }
            if (b.color || a.color) {
                var v = b.color || a.color;
                t.push('ss:Color="' + v + '"');
            }
            if (b.size || a.size) {
                var v = b.size || a.size;
                t.push('ss:Weight="' + v + '"');
            }

            if (t.length) {
                return t;
            } else {
                return false;
            }
        }

        var a = null;
        var b = null;
        var t = null;

        if (css['border']) {
            a = plugin.parseBorderCSS(css['border']);
        }

        if (css['border-top']) {
            b = plugin.parseBorderCSS(css['border-top']);
        } else {
            b = null;
        }

        if (t = getBorder(a, b)) {
            Borders.push('<Border ss:Position="Top" ss:LineStyle="Continuous" ' + t.join(' ') + '></Border>');
        }

        if (css['border-bottom']) {
            b = plugin.parseBorderCSS(css['border-bottom']);
        } else {
            b = null;
        }

        if (t = getBorder(a, b)) {
            Borders.push('<Border ss:Position="Bottom" ss:LineStyle="Continuous" ' + t.join(' ') + '></Border>');
        }

        if (css['border-left']) {
            b = plugin.parseBorderCSS(css['border-left']);
        } else {
            b = null;
        }

        if (t = getBorder(a, b)) {
            Borders.push('<Border ss:Position="Left" ss:LineStyle="Continuous" ' + t.join(' ') + '></Border>');
        }

        if (css['border-right']) {
            b = plugin.parseBorderCSS(css['border-right']);
        } else {
            b = null;
        }

        if (t = getBorder(a, b)) {
            Borders.push('<Border ss:Position="Right" ss:LineStyle="Continuous" ' + t.join(' ') + '></Border>');
        }

        if (Borders.length > 0) {
            style.push('<Borders>' + Borders.join('') + '</Borders>');
        }

        if (css['background-color']) {
            var v = plugin.rgbToHex(css['background-color']);
            style.push('<Interior ss:Color="' + v + '" ss:Pattern="Solid"/>');
        }

        return style.join('');
    }

    /**
     * Create a new workbook
     */
    plugin.createWorkbook = function (o) {
        var content = '';
        for (var i = 0; i < worksheets.length; i++) {
            content += worksheets[i].data;
        }
        var style = '';
        var keys = Object.keys(styles);
        for (var i = 0; i < keys.length; i++) {
            style += '<Style ss:ID="' + styles[keys[i]].id + '">' + styles[keys[i]].content + '</Style>';
        }
        return `<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"><Author>${o.author}</Author><Created>${o.created}</Created></DocumentProperties><Styles>${style}<Style ss:ID="Currency"><NumberFormat ss:Format="Currency"></NumberFormat></Style><Style ss:ID="calendar"><NumberFormat ss:Format="yyyy-mm-dd hh:mm:ss"></NumberFormat></Style></Styles>${content}</Workbook>`;
    }

    /**
     * Create a worksheet object
     */
    plugin.createWorksheet = function (jexcelInstance) {
        // Create worksheet object
        var worksheet = helpers();
        worksheet.instance = jexcelInstance;
        worksheet.name = jexcelInstance.options.worksheetName.replace(' ', '');

        // Execute
        worksheet.getData();

        // Create container
        worksheets.push(worksheet);
    }

    /**
     * Rgb to hex helpers
     */
    plugin.rgbToHex = function (color) {
        color = "" + color;
        if (!color || color.indexOf("rgb") < 0) {
            return color;
        }

        if (color.charAt(0) == "#") {
            return color;
        }

        var nums = /(.*?)rgb\((\d+),\s*(\d+),\s*(\d+)\)/i.exec(color),
            r = parseInt(nums[2], 10).toString(16),
            g = parseInt(nums[3], 10).toString(16),
            b = parseInt(nums[4], 10).toString(16);

        return "#" + (
            (r.length == 1 ? "0" + r : r) +
            (g.length == 1 ? "0" + g : g) +
            (b.length == 1 ? "0" + b : b)
        );
    }

    /**
     * Generate excel file from jexcel
     */
    plugin.generate = function (filename, author) {
        // if (! filename) {
        //     filename = 'TKL';
        // }

        if (!author) {
            author = 'Takeoff Lite';
        }

        // Get worksheets
        if (Array.isArray(el.jexcel)) {
            for (var t = 0; t < el.jexcel.length; t++) {
                plugin.createWorksheet(el.jexcel[t]);
            }
        } else {
            plugin.createWorksheet(el.jexcel);
        }

        // Workbook
        var workbook = plugin.createWorkbook({
            create: (new Date()).getTime(),
            author: author,
        });

        // Blob
        var blob = new Blob(["\uFEFF" + workbook], { type: 'application/vnd.ms-excel;charset=utf-8;' });

        // IE Compatibility
        if (window.navigator && window.navigator.msSaveOrOpenBlob) {
            window.navigator.msSaveOrOpenBlob(blob, projectName + '.xls');
        } else {
            // Download element
            var pom = document.createElement('a');
            var url = URL.createObjectURL(blob);
            pom.href = url;
            pom.setAttribute('download', projectName + '.xls');
            document.body.appendChild(pom);
            pom.click();
            pom.parentNode.removeChild(pom);
        }
    }();
});
