/**
 * Takeofflite drawing
 * @dependencies:  jQuery, draw2d
 * @copyright: 2020 - All Rights Reserved
 * @License: Private
 * @author: Beehive Web Solutions / contact@beehivews.com
 */

// TODO: MOST OF WHAT IS BELOW NEEDS TO BE ORGANIZED AND REFACTORED INTO OBJECTS/CLASSES...
// COPYING RAW CODE FROM ASP FILE INTO GLOBAL SCOPE HERE FOR NOW:

// legacy vars
var canvas;
var context;
var doFirstLineUX = true;
var line;
var PolyLine;
var Polygon;
var PolygonNew = false;
var rect1;
var svg;
var xs;
var HeightDefault;
var gfx_holder = getDrawCanvasWrapperEl();

/**
 * Initialize Drawing
 * @returns {undefined}
 */
function initTOLDrawing(){

    console.log('[initDrawing] Initializing TOL DRAWING.');

    //TODO: this init order is having some issues and is still being refactored

    // init draw 2d, creates the canvas
    initDraw2DCanvas(); // this has dependencies that are out of order

    // creates the drawing mode svg
    initSVG();

    // loads up the design data, gets the sheet object, parses it and sends to draw2d svg
    legacyDrawingInit();

    //drawCanvasImg(); // this has dependencies that are out of order

    // add info bar
    addDrawingInfoBar();
}

function addDrawingInfoBar(){

    var ft = $('#txtFeet').val();
    var inch = $('#txtInch').val();
    $('#gfx_holder').parent().append('Scale: ' + ft + "' " + inch + '"')
}

function legacyDrawingInit(){

    $('#liScale' + drawingData.scaleId).addClass("liAclive");//?

    // get the design as a JSON obj from page or server...
    var jsonDocument1 = GetSheetObject(true);

    trace('[initDraw2DCanvas] Reloading design from data: ', jsonDocument1);

    // unmarshal the JSON document into the canvas
    // (load)
    var reader = new draw2d.io.json.Reader();
    reader.unmarshal(canvas, jsonDocument1);

    // display/write the SVG into the preview DIV
    showSVG(canvas);

    // add an event listener to the Canvas for change notifications.
    // We just dump the current canvas document into the DIV
    //
    canvas.getCommandStack().addEventListener(function (e) {
        if (e.isPostChangeEvent()) {
            showSVG(canvas);
        }
    });

    // show/hide some lines? TODO: seeems unnecessary
    hideFirstLine();

    // bind click events
    bindDrawingMouseEvents();
}


/**
 * Stop drawing
 * @returns {undefined}
 */
function StopDrawing() {

    trace('[StopDrawing]');

    onDrawingToolComplete('Recent Drawing Tool');

    if (Polygon == false) {

        canvas.remove(PolyLinePL);
        DeleteObject(PolyLinePL);
        Polygon = new Rectangle();
        // Polygon.setColor("#1d1dff");

        Polygon.attr({
            bgColor: $('#txtColorObj').val(),
            color: $('#txtColorObj').val(),
            alpha: 0.7
        });

        var minarrx = [];
        var minarry = [];

        for (var s = 0; s < Vertex.length; s++) {
            minarrx[s] = Vertex[s]['x0'];
            minarry[s] = Vertex[s]['y0'];
        }
        var minx = Math.min.apply(Math, minarrx);
        var miny = Math.min.apply(Math, minarry);

        Polygon.attr({
            id: $('#txtNameObj').val()
        });
        canvas.add(Polygon, minx, miny);

        a = -1;
        isDrawing = true;
        PolygonNew = false;
        Vertex = [];


        Vertices = [];
        svg.onmousemove = null;
        PolyLinePL = true;
        line = true;
        SavePolygon(Polygon);
    }


    if ((PolyLine != true)) {
        SaveLines(PolyLine);
        isDrawing = true;
    }

    if (Line == false) {
        SaveLines(line);
        isDrawing = true;
    }

    document.getElementById('aLine').style.color = "";
    document.getElementById('aPolyLine').style.color = "";
    document.getElementById('aPolygon').style.color = "";
    document.getElementById('aMove').style.color = "";
    document.getElementById('aScale').style.color = "";
    document.querySelector('svg').style.cursor = "";
    document.getElementById('gfx_holder').style.cursor = "";
    document.getElementById('aPoint').style.color = "";
    svg.onmousedown = null;
    document.getElementById("gfx_holder").onmousedown = null;
    document.getElementById("gfx_holder").onmousemove = null;
    document.getElementById("gfx_holder").onmouseup = null;
    svg.onmousemove = null;
    $('#sweeties').css({"color": "  white"});
    $('#ulScale').css({"display": " none"});
    ShoweMenu = true;
    svg.onmouseup = null;
    Polygon = true;
    Line = true;
    PolyLine = true;
    Point = true;
}

var i = 0;
var Vertices = [];

/**
 *
 * @param {type} e
 * @returns {undefined}
 */
function startDrawingPolyLine(e) {

    if (event.ctrlKey || event.metaKey || event.which == 3) {
        SaveLines(PolyLine);
        isDrawing = true;
        svg.onmousemove = null;
    } else {
        var drawingPt = getDrawingPointFromE(e);
        var divCont = document.getElementById("divCont");
        var gfx_holder = getDrawCanvasWrapperEl();
        if (isDrawing == true) {
            //41154
            line = new draw2d.shape.basic.Line();
            Vertices = [];
//			var x1 = getDrawingPtFromE(e).x;
//			var y1 = getDrawingPtFromE(e).y;
            var x1 = drawingPt.x;
            var y1 = drawingPt.y;

            isDrawing = false;
            PolyLine = new draw2d.shape.basic.PolyLine();
            Vertices[i] = {x: x1, y: y1};
            line.setStartPoint(x1, y1);
            line.setEndPoint(x1, y1);
            line.attr({
                color: $('#txtColorObj').val()
            });
            canvas.add(line);
            svg.onmousemove = StopDrawingLine;
        } else {
//			var x1 = getDrawingPtFromE(e).x;
//			var y1 = getDrawingPtFromE(e).y;
            var x1 = drawingPt.x;
            var y1 = drawingPt.y;

            Vertices[i] = {x: x1, y: y1};

            PolyLine.setStroke(2);
            //  PolyLine.setColor("#1d1dff");
            PolyLine.setVertices(Vertices);
            PolyLine.attr({
                id: $('#txtNameObj').val(),
                color: $('#txtColorObj').val()
            });
            canvas.add(PolyLine);

            canvas.remove(line);
            DeleteObject(line);
            svg.onmousemove = StopDrawingPolyLine;


        }
        i++;
    }
}

/**
 *
 * @param {type} e
 * @returns {undefined}
 */
function StopDrawingPolyLine(e) {

    var divCont = document.getElementById("divCont");
    var gfx_holder = getDrawCanvasWrapperEl();
    if ((e.pageX > (divCont.offsetLeft + divCont.clientWidth) - 150)) {
        MoveMousemoveX(e);
    } else if ((e.pageX < (divCont.offsetLeft) + 150)) {
        MoveMousemove2X(e);
    }


    if ((e.pageY > (divCont.offsetTop + divCont.clientHeight) - 50)) {
        MoveMousemoveY(e);
    } else if ((e.pageY < (divCont.offsetTop) + 150)) {
        MoveMousemove2Y(e);
    }

    var x1 = getDrawingPtFromE(e).x;
    var y1 = getDrawingPtFromE(e).y;

    Vertices[i] = {x: x1, y: y1};

    PolyLine.setStroke(2);
    //PolyLine.setColor("#1d1dff");
    PolyLine.setVertices(Vertices);

    canvas.add(PolyLine);

    //   canvas.remove(line);

}

var x0 = 0;
var y0 = 0;

var Vertex = [];

//? ///** @lends draw2d.shape.dimetric.Rectangle.prototype */
var Rectangle = draw2d.shape.basic.Polygon.extend({
    NAME: "draw2d.shape.dimetric.Rectangle",
    /**
     * Creates a new figure element which are not assigned to any canvas.
     *
     * @param {Object} [attr] the configuration of the shape
     */
    init: function init(attr, setter, getter) {
        this._super(extend({bgColor: "#1d1dff", color: "#1d1dff"}, attr), setter, getter);
        var pos = this.getPosition();
        this.resetVertices();
        //  this.id="sfsff";
        for (var s = 0; s < Vertex.length; s++) {
            this.addVertex(Vertex[s]['x0'], Vertex[s]['y0']);
        }
        this.setPosition(pos);
    }
});

var a = 0;
var Vertices = [];

//  var Polygon;
var PolyLinePL;
function startDrawingPolygon(e) {

    if (event.ctrlKey || event.metaKey || event.which == 3) {
        // if (a>3)
        {
            canvas.remove(PolyLinePL);
            DeleteObject(PolyLinePL);
            Polygon = new Rectangle();
            // Polygon.setColor("#1d1dff");

            Polygon.attr({bgColor: $('#txtColorObj').val(), color: $('#txtColorObj').val(), alpha: 0.7});

            var minarrx = [];
            var minarry = [];

            for (var s = 0; s < Vertex.length; s++) {
                minarrx[s] = Vertex[s]['x0'];
                minarry[s] = Vertex[s]['y0'];
            }
            var minx = Math.min.apply(Math, minarrx);
            var miny = Math.min.apply(Math, minarry);

            Polygon.attr({
                id: $('#txtNameObj').val()
            });
            canvas.add(Polygon, minx, miny);

            a = -1;
            isDrawing = true;
            PolygonNew = false;
            Vertex = [];


            Vertices = [];
            svg.onmousemove = null;


            SavePolygon(Polygon);
        }
    } else {
        var divCont = document.getElementById("divCont");
        var gfx_holder = getDrawCanvasWrapperEl();

        if (isDrawing == true) {

            isDrawing = false;
            x0 = getDrawingPtFromE(e).x;
            y0 = getDrawingPtFromE(e).y;


            line = new draw2d.shape.basic.Line();
            Vertices = [];


            Vertex[a] = {x0, y0};

            PolyLinePL = new draw2d.shape.basic.PolyLine();
            Vertices[a] = {x: x0, y: y0};
            line.setStartPoint(x0, y0);
            line.setEndPoint(x0, y0);
            line.attr({

                color: $('#txtColorObj').val()
            });
            canvas.add(line);
            svg.onmousemove = StopDrawingLine;

        } else {
            x0 = getDrawingPtFromE(e).x;
            y0 = getDrawingPtFromE(e).y;

            Vertices[a] = {x: x0, y: y0};
            canvas.remove(line);
            DeleteObject(line);
            Vertex[a] = {x0, y0};
            PolyLinePL.setStroke(2);
            PolyLinePL.attr({bgColor: $('#txtColorObj').val(), color: $('#txtColorObj').val(), alpha: 0.7});
            // PolyLine.setColor("#1d1dff");
            PolygonNew = true;

            PolyLinePL.attr({
                id: "PolygonNew"
            });
            PolyLinePL.setVertices(Vertices);

            canvas.add(PolyLinePL);
            svg.onmousemove = StopDrawingPolygon;
            // canvas.setCurrentSelection(PolyLine);
        }

    }

    a++;
}

function StopDrawingPolygon(e) {

    var divCont = document.getElementById("divCont");
    var gfx_holder = getDrawCanvasWrapperEl();
    if ((e.pageX > (divCont.offsetLeft + divCont.clientWidth) - 150)) {
        MoveMousemoveX(e);
    } else if ((e.pageX < (divCont.offsetLeft) + 150)) {
        MoveMousemove2X(e);
    }


    if ((e.pageY > (divCont.offsetTop + divCont.clientHeight) - 50)) {
        MoveMousemoveY(e);
    } else if ((e.pageY < (divCont.offsetTop) + 150)) {
        MoveMousemove2Y(e);
    }




    x0 = getDrawingPtFromE(e).x;
    y0 = getDrawingPtFromE(e).y;



    Vertices[a] = {x: x0, y: y0};
    //   canvas.remove(line);
    Vertex[a] = {x0, y0};
    PolyLinePL.setStroke(2);
    PolyLinePL.attr({bgColor: $('#txtColorObj').val(), color: $('#txtColorObj').val(), alpha: 0.7});

    //  PolyLine.setColor("#1d1dff");
    PolyLinePL.setVertices(Vertices);

    canvas.add(PolyLinePL);

}

/**
 * create a drawig object - starts a mode / drawing tool
 * @param {type} name
 * @returns {undefined}
 */
function CreateObj(name) {

    console.log('[createObj] Creating drawing object: ' + name);

    $('#ErrorMessage').text("");
    $('#txtNameObj').val("");

    if (doFirstLineUX != true) {

        takeOffLite.popUpShow2();

        switch(name){
            case "Line": AddLine();
                break;
            case "Polyline": AddPolyLine();
                break;
            case "Polygon": AddPolygon();
                break;
            case "Point": AddPoint();
                break;
        }

        onDrawingToolActive(name);


    } else {

        // scale popup for first line
        FirstScale();
    }
}

/**
 * get the offset of the drawing area
 * @returns {getDrawingAreaOffset.takeofflite-drawingAnonym$21}
 */
function getDrawingAreaOffset() {

    var gfx_holder = getDrawCanvasWrapperEl();
    return {
        x: gfx_holder.scrollLeft,
        left: gfx_holder.scrollLeft,
        y: gfx_holder.scrollTop,
        top: gfx_holder.scrollTop
    };
}


var header$;
/**
 * get the header container element
 * @returns {$}
 */
function getHeaderContainer$(){
    if(isUndefined(header$) || (header$.length > 0))
        header$ = $('.pure-menu');
    return header$;
}

/**
 * get drawing pt
 * @param {type} __e
 * @returns {undefined}
 */
function getDrawingPointFromE(__e) {

    // crate the point
    var drawingOffsetPt = getDrawingAreaOffset();
    var divCont = document.getElementById("divCont");
    //var gfx_holder = getDrawCanvasWrapperEl();

    // orig:
    // var x1 = (e.pageY - divCont.offsetLeft + gfx_holder.scrollLeft) * canvas.getZoom()
    // var y1 = (e.pageY - divCont.offsetTop + gfx_holder.scrollTop) * canvas.getZoom();

    var x = (__e.pageX - divCont.offsetLeft + drawingOffsetPt.left) * canvas.getZoom();
    var y = (__e.pageY - divCont.offsetTop + drawingOffsetPt.top) * canvas.getZoom();

    // adjust for header height
    var headerHeight = getHeaderContainer$().height();
    var origY = y;
    y -= headerHeight;

    trace('y height adjustment for header height: ' + headerHeight + ' from ' + origY + ' to ' + y);

    return {
        x: x,
        y: y,
        left: x,
        top: y
    };
}
// expose shorter name
window.getDrawingPtFromE = window.getDrawingPointFromE = getDrawingPointFromE;

var GroupPoint;
function startDrawingPoint(e) {


    console.log('[startDrawingPoint] Starting a drawing point...');

    if (event.ctrlKey || event.metaKey || event.which == 3) {

        // ?
        ClearBoot();

    } else {

        // create the point
        if (!isUndefined(isDrawing) && isDrawing === true)
        {
            var drawingPt = getDrawingPointFromE(e);
            var Point = new draw2d.shape.basic.Oval({
                width: 10,
                height: 10,
                x: drawingPt.x,
                y: drawingPt.y});

            // set the point attrs
            Point.attr({
                color: $('#txtColorObj').val(),
                bgColor: $('#txtColorObj').val()
            });

            canvas.add(Point);
            AddNewPoint(Point);
        }
    }
}

var Point = true;
function AddPoint() {
    if (Point == true) {
        GroupPoint = Math.floor(Math.random() * (1000 - 0)) + 0;
        document.getElementById('aPoint').style.color = "grey";
        svg.onmousedown = startDrawingPoint;

        svg.contextmenu = Clear;
        svg.oncontextmenu = Clear;
        document.getElementById('aLine').style.color = "";
        document.getElementById('aPolyLine').style.color = "";
        document.getElementById('aPolygon').style.color = "";
        document.getElementById('aMove').style.color = "";
        document.querySelector('svg').style.cursor = "crosshair";
        document.getElementById('gfx_holder').style.cursor = "crosshair";
        $('#sweeties').css({"color": "  white"});
        $('#ulScale').css({"display": " none"});
        ShoweMenu = true;
        Point = false;
        Line = true;
        PolyLine = true;
        Polygon = true;

    } else {
        ClearBoot();

    }

}

var Line = true;
function AddLine() {
    if (Line == true) {

        document.getElementById('aLine').style.color = "grey";
        svg.onmousedown = startDrawingLine;
        //   svg.onmouseup = ferstDrawingPolyLine;
        svg.contextmenu = Clear;
        svg.oncontextmenu = Clear;
        document.getElementById('aPoint').style.color = "";
        document.getElementById('aPolyLine').style.color = "";
        document.getElementById('aPolygon').style.color = "";
        document.getElementById('aMove').style.color = "";
        document.querySelector('svg').style.cursor = "crosshair";
        document.getElementById('gfx_holder').style.cursor = "crosshair";
        $('#sweeties').css({"color": "  white"});
        $('#ulScale').css({"display": " none"});
        ShoweMenu = true;
        Line = false;
        PolyLine = true;
        Polygon = true;
        Point = true;

    } else {
        ClearBoot();
    }
}

var PolyLine = true;
function AddPolyLine() {
    if (doFirstLineUX != true) {
        if (PolyLine == true) {
            document.getElementById('aPolyLine').style.color = "grey";
            svg.onmousedown = startDrawingPolyLine;

            svg.contextmenu = Clear;
            svg.oncontextmenu = Clear;
            document.getElementById('aPoint').style.color = "";
            document.getElementById('aLine').style.color = "";
            document.getElementById('aPolygon').style.color = "";
            document.getElementById('aMove').style.color = "";
            document.querySelector('svg').style.cursor = "crosshair";
            document.getElementById('gfx_holder').style.cursor = "crosshair";
            $('#sweeties').css({"color": "  white"});
            $('#ulScale').css({"display": " none"});
            ShoweMenu = true;
            PolyLine = false;
            Line = true;
            Polygon = true;
            Point = true;


        } else {
            ClearBoot();

        }
    } else {
        FirstScale();
    }
}

var Polygon = true;
function AddPolygon() {
    if (doFirstLineUX != true) {
        if (Polygon == true) {
            document.getElementById('aPolygon').style.color = "grey";
            svg.onmousedown = startDrawingPolygon;
            svg.contextmenu = Clear;
            svg.oncontextmenu = Clear;
            document.getElementById('aPoint').style.color = "";
            document.getElementById('aLine').style.color = "";
            document.getElementById('aPolyLine').style.color = "";
            document.getElementById('aMove').style.color = "";
            document.querySelector('svg').style.cursor = "crosshair";
            document.getElementById('gfx_holder').style.cursor = "crosshair";
            $('#sweeties').css({"color": "  white"});
            $('#ulScale').css({"display": " none"});
            ShoweMenu = true;
            Polygon = false;
            Line = true;
            PolyLine = true;
            Point = true;
        } else {
            ClearBoot();

        }
    } else {
        FirstScale();
    }
}


function  Clear(e) {
    document.getElementById('aLine').style.color = "";
    document.getElementById('aPolyLine').style.color = "";
    document.getElementById('aPolygon').style.color = "";
    document.getElementById('aMove').style.color = "";
    document.getElementById('aScale').style.color = "";
    document.querySelector('svg').style.cursor = "";
    document.getElementById('gfx_holder').style.cursor = "";
    document.getElementById('aPoint').style.color = "";
    svg.onmousedown = null;
    // svg.contextmenu= null;
    // svg.oncontextmenu= null;
    e.preventDefault();
    svg.onmousemove = null;
    document.getElementById("gfx_holder").onmousedown = null;
    document.getElementById("gfx_holder").onmousemove = null;
    document.getElementById("gfx_holder").onmouseup = null;
    $('#sweeties').css({"color": "  white"});
    $('#ulScale').css({"display": " none"});
    ShoweMenu = true;
    svg.onmouseup = null;
    Polygon = true;
    Line = true;
    PolyLine = true;
    Point = true;
}

function  ClearBoot() {
    document.getElementById('aLine').style.color = "";
    document.getElementById('aPolyLine').style.color = "";
    document.getElementById('aPolygon').style.color = "";
    document.getElementById('aMove').style.color = "";
    document.getElementById('aScale').style.color = "";
    document.querySelector('svg').style.cursor = "";
    document.getElementById('gfx_holder').style.cursor = "";
    document.getElementById('aPoint').style.color = "";
    svg.onmousedown = null;
    document.getElementById("gfx_holder").onmousedown = null;
    document.getElementById("gfx_holder").onmousemove = null;
    document.getElementById("gfx_holder").onmouseup = null;
    svg.onmousemove = null;
    $('#sweeties').css({"color": "  white"});
    $('#ulScale').css({"display": " none"});
    ShoweMenu = true;
    svg.onmouseup = null;
    Polygon = true;
    Line = true;
    PolyLine = true;
    Point = true;
}

/**
 * Initialize the draw 2d canvas... this gets the sheet object from the server
 * @note: this is being refactored.
 * @returns {undefined}
 */
function initDraw2DCanvas() {

    var canvasWrapperID = 'gfx_holder';
    var canvasWrapperSel = '#'+canvasWrapperID;
    var canvasWrapper$ = $(canvasWrapperSel);
    var canvasWrapperEl = canvasWrapper$[0];

    // canas wrapper
    gfx_holder = canvasWrapperEl;
    //canvasWrapper$.css("display", "");//?

    // canvas
    // create the canvas for the user interaction

    canvas = new draw2d.Canvas(canvasWrapperID);
    document.getElementById('divView').disabled = 'none';
    canvas.setScrollArea(canvasWrapperSel);


    /*canvas.installEditPolicy( new draw2d.policy.canvas.DefaultKeyboardPolicy());*/
    canvas.installEditPolicy(new draw2d.policy.canvas.WheelZoomPolicy());                // Responsible for zooming with mouse wheel
    canvas.installEditPolicy(new draw2d.policy.canvas.DefaultKeyboardPolicy());          // Handles the keyboard interaction
    canvas.installEditPolicy(new draw2d.policy.canvas.BoundingboxSelectionPolicy());     // Responsible for selection handling
    canvas.installEditPolicy(new draw2d.policy.canvas.DropInterceptorPolicy());          // Responsible for drop operations
}

/**
 * remove first line
 * @returns {undefined}
 */
function removeFirstLine(){
    var figure = canvas.getLines();
    for (var i = 0; i < figure['data'].length; i++) {
        if (figure['data'][i]['id'] == 'FirstLine') {
            canvas.remove(figure['data'][i]);
        }
    }
}

/**
 * init lines
 * @returns {undefined}
 */
function hideFirstLine(){
    var figure = canvas.getLines();
    for (var i = 0; i < figure['data'].length; i++) {
        if (figure['data'][i]['id'] == 'FirstLine') {
            trace('[hideFirstLine] Hiding first line ', figure['data'][i]);
            figure['data'][i].setVisible(false);
        }
    }
}

/**
 * bind click events for the drawing area
 * @returns {undefined}
 */
function bindDrawingMouseEvents(){
    $('#draw2d').click(function (ev) {
        console.log("Mouse click:" + ev.clientX + "," + ev.clientY);
    });

    // Inject the ZoomIn Button and the callbacks
    $("#canvas_zoom_in").on("click", function () {
        setZoom(canvas.getZoom() * 1.2, true);
    });

    // Inject the OneToOne Button
    $("#canvas_zoom_normal").on("click", function () {
        setZoom(1.0, false);

    });

    // Inject the ZoomOut Button and the callback
    //
    $("#canvas_zoom_out").on("click", function () {
        setZoom(canvas.getZoom() * 0.8, true);

    });
}

/**
 * write the svg to the page
 * @param {type} canvas
 * @returns {undefined}
 */
function showSVG(canvas) {

    trace('Creating SVG... in: ', getDrawCanvasWrapperEl() );

    //
    var writer = new draw2d.io.svg.Writer();
    writer.marshal(canvas, function (svg) {
        //$("#log").text(svg); //?
        console.log('Marshalled canvas to svg. ');
    });

    //
    $('#gfx_holder').show();
}




var zoom = new draw2d.policy.canvas.WheelZoomPolicy();
/**
 * set zoom function
 * @type Function
 */
var setZoom = $.proxy(function (newZoom, animate) {
    var bb = svg.getBoundingClientRect();
    var c = $('#gfx_holder');
    canvas.setZoom(newZoom, true);
    c.scrollTop((bb.y / newZoom - c.height() / 2));
    c.scrollLeft((bb.x / newZoom - c.width() / 2));
    $("#canvas_zoom_normal").text((parseInt((1.0 / newZoom) * 100)) + "%");
}, this);

/**
 * create the svg and add to page...
 * note: this was done somewhere else originally but can't find it so adding a new one now.
 * @returns {undefined}
 */
function createSVG(){
    $('#gfx_holder').prepend('<svg height="100%" width="100%"></svg>');
    svg$ = $('svg');
    svg = svg$[0];
}

/**
 * Initialize the drawing SVG
 * NOTE: this has to come after the SVG is created by draw2d
 * @returns {undefined}
 */
function initSVG() {

    var svg$ = $('svg');
    svg = svg$[0];
    var hasSVG = svg$.length > 0 && !isUndefined(svg);

    if(!hasSVG) {
        console.log('[initTOLDrawing] Warning: SVG not found, adding one now.: ', svg$);
        createSVG();
    }

    console.log('[initSVG] Initializing SVG', svg);

    //test.style.position="absolute";
    svg.style.top = "";
    svg.style.left = "";
    svg.style.position = "";
    svg.style.zIndex = 100;
    svg.oncontextmenu = function () {
        Line = true
        svg.onmousedown = null;
    }

    var sheight = svg.getAttribute('height');
    var swidth = svg.getAttribute('width');
    HeightDefault = sheight;
    // if('<%= sFileEmpty%>'!='true')
    //  {
    //      svg.innerHTML='<%=sFile%>';
    //      FirstLine=false;
    //   }else

    //{  // what is this... orphaned curlies?
    //}

    var imgUrl = drawingData.imgUrl;
    svg.innerHTML = "<image id='imgSVG' x='0' y='0' height='" + sheight + "'  width='" + swidth + "' href='" + imgUrl + "'></image>";

    //$('#imgSVG').css('opacity',.5);

    trace('[initSVG] SVG inner HTML set.',svg);
}

var isDrawing = true;
var isFirstLine2Point = false;
function ferstDrawingLine() {
    if (isFirstLine2Point == true) {
        if (doFirstLineUX == true) {

            doFirstLineUX = false;
            ClearBoot; // pretty sure this would do nothing... missing ()?
            //  popUpShow();
        }
    }
}

function getDrawCanvasWrapperEl(){
    return document.getElementById("gfx_holder");
}

/**
 * user started drawing an object
 * @param {type} e
 * @returns {undefined}
 */
function onDrawingToolActive(__toolName){
    trace('[onDrawingToolActive] Drawing tool mode started: ' + __toolName);
    $('body').addClass('drawing-tool-active');
}

function onDrawingToolComplete(__toolName){
    trace('[onDrawingToolComplete] Drawing tool mode completed: ' + __toolName);
    $('body').removeClass('drawing-tool-active');
}

function startDrawingLine(e) {

    if (event.ctrlKey || event.metaKey || event.which == 3) {
        isDrawing = true;
    } else {

        var divCont = document.getElementById("divCont");
        var gfx_holder = getDrawCanvasWrapperEl();
        var notFirstLine = doFirstLineUX === false;
        if (notFirstLine) {

            //TODO: fix the positioning of drawing, it is offset and hardcoded values are not dynamic enough (68?):
            if (isDrawing == true) {

                trace('Drawing line... offsetsX: ', [e.pageX, divCont.offsetLeft, gfx_holder.scrollLeft ]);
                trace('Drawing line... offsetsY: ', [e.pageY, divCont.offsetTop, gfx_holder.scrollTop ]);

                var zoomFactor = canvas.getZoom() || 1;
                trace('  ... current zoom: ', zoomFactor);

                line = new draw2d.shape.basic.Line();

                // shift for page position / offsets
                var x = getDrawingPtFromE(e).x;
                var y = getDrawingPtFromE(e).y;

                line.setStartPoint(x, y);
                line.setEndPoint(x, y);
                line.attr({
                    id:		$('#txtNameObj').val(),
                    color:	$('#txtColorObj').val()
                });
                canvas.add(line);
                isDrawing = false;
                svg.onmousemove = StopDrawingLine;
            } else {
                isFirstLine2Point = true;
                SaveLines(line);
                ClearBoot();
                svg.onmousemove = null;
                isDrawing = true;
            }
        } else { // IS FIRST LINE

            if (isDrawing == true) {
                removeFirstLine(); //?

                line = new draw2d.shape.basic.Line();

                var x = getDrawingPtFromE(e).x;
                var y = getDrawingPtFromE(e).y;
                line.setStartPoint(x, y);
                line.setEndPoint(x, y);

                line.setColor("#ff0800");
                line.attr({	id: "FirstLine"	});
                $("#FirstLineMsg").css('left', (e.pageX + 10) + 'px').css('top', (e.pageY + 10) + 'px');
                //   $("#FirstLineMsg").show();

                $("#FirstLineMsg").text('Click Second Point of Dimension for Scaling ');
                document.querySelector('svg').style.cursor = "crosshair";
                canvas.add(line);
                isDrawing = false;
                svg.onmousemove = StopDrawingLine;
            } else {
                isFirstLine2Point = true;
                SaveLines(line)
                doFirstLineUX = false;
                $("#FirstLineMsg").text('Click First Point of Dimension for Scaling ');
                $("#FirstLineMsg").hide();
                ClearBoot();

                var figure = canvas.getLines();

                for (var i = 0; i < figure['data'].length; i++) {

                    if (figure['data'][i]['id'] == 'FirstLine') {
                        figure['data'][i].setVisible(false);
                    }
                }
                svg.onmousemove = null;
                isDrawing = true;
            }
        }
    }
}

/**
 * Gets a drawing objects info from server... this gets called a lot
 * @param {type} ID
 * @returns {undefined}
 */
function GetObjectInfo(ID) {
    TakeOffLite.startProcess('GetObjectInfo');
    $.ajax({
        url: 'Projects.asmx/GetObjectInfo',
        data: {
            Client: projectVars.userId,
            ProjectID: projectVars.id,
            SheetID: urlVars.sh,
            ID: ID,
        },
        async: false,
        method: 'post',
        dataType: 'xml',
        success: function (msg) {

            TakeOffLite.endProcess('GetObjectInfo');

            $(msg).find('Table').each(function (i, row) {
                var dropZone = document.getElementById('InfoObject');
                if ($(row).find('Type').text() == 'draw2d.shape.basic.Polygon') {

                    dropZone.innerHTML = ('Name:' + ID + '<br> Perimeter :' + $(row).find('Perimeter').text() + '<br> Area :' + $(row).find('Area').text());
                } else if ($(row).find('Type').text() == 'draw2d.shape.basic.Line') {

                    dropZone.innerHTML = ('Name:' + ID + '<br>Length :' + $(row).find('Perimeter').text());
                } else if ($(row).find('Type').text() == 'draw2d.shape.basic.PolyLine') {

                    dropZone.innerHTML = ('Name:' + ID + '<br> Length :' + $(row).find('Perimeter').text());
                } else if ($(row).find('Type').text() == 'draw2d.shape.basic.Oval') {

                    dropZone.innerHTML = ('Name:' + $(row).find('Name').text());
                } else {
                    dropZone.innerHTML = ('');
                }
            });
        },
        error: errorHandlerMisc
    });
}



var activeObjectInfoId = 'none';

/**
 * mouse over a drawing object
 * @returns {undefined}
 */
function onMouseEnterDrawingObject(__this){

    trace('[onMouseEnterDrawingObject] Moused over obj. __this: ', __this);

    // TODO: this function is called from code hacked into draw2d lib, which should be moved.
    var isLine = (PolyLine == true) && (Polygon == true) && (Line == true);
    if (isLine) { showDrawingObjToolTip(__this); }
}

/**
 * showing drawing object tool tip when mouse over
 * @param {type} __this	The reference to "this" from the original event handler
 * @returns {undefined}
 */
function showDrawingObjToolTip(__this){

    if( !isUndefined(__this) ){

        trace('[showDrawingObjToolTip] target ID vs/ last ID: '
            + __this.id
            + ' / '
            + activeObjectInfoId );

        if(__this.id !== activeObjectInfoId){
            trace('[showDrawingObjToolTip] Getting object data for mouse-over object ', __this);
            GetObjectInfo(__this.id);
            activeObjectInfoId = __this.id;
            svg.onmousemove = ShowObjectInfo;
        } else {
            trace('[onMouseEnterDrawingObject] Object info already current.  Not requesting again. ID: ' + __this.id );
        }
    }

    // show the element
    $("#InfoObject").show();
}

/**
 * Show the object info tooltip - used as event handler for mouse events
 * @param {type} e
 * @returns {undefined}
 */

function ShowObjectInfo(e) {

    var mouseOffsetX = 10;
    var mouseOffsetY = 10;

    var posX = (e.pageX + mouseOffsetX);
    var posY = (e.pageY + mouseOffsetY);
    posY -= getHeaderContainer$().height(); // corrects the header offset issue

    // update tooltip pos
    $("#InfoObject")
        .css('left', String(posX) + 'px')
        .css('top', String(posY) + 'px');
}

function StopDrawingLine(e) {

    var divCont = document.getElementById("divCont");
    var gfx_holder = getDrawCanvasWrapperEl();
    if ((e.pageX > (divCont.offsetLeft + divCont.clientWidth) - 150)) {
        MoveMousemoveX(e);
    } else if ((e.pageX < (divCont.offsetLeft) + 150)) {
        MoveMousemove2X(e);
    }


    if ((e.pageY > (divCont.offsetTop + divCont.clientHeight) - 50)) {
        MoveMousemoveY(e);
    } else if ((e.pageY < (divCont.offsetTop) + 150)) {
        MoveMousemove2Y(e);
    }


    var x = getDrawingPtFromE(e).x;
    var y = getDrawingPtFromE(e).y;
    line.setEndPoint(x, y);
    line.setStroke(2);
    if (doFirstLineUX == true) {
        showFirstLineMsg(e);
        line.setColor("#ff0800");
        line.attr({
            id: "FirstLine"
        });
        document.querySelector('svg').style.cursor = "crosshair";
    } else {
        //  line.setColor("#1d1dff");
    }

    // canvas.add(line);
}


function DeleteObject(obj) {
    var Id = obj.id;
    if (Id == 'FirstLine') {
        doFirstLineUX = true;
        isFirstLine2Point = false;
    }
    if (obj.cssClass != "draw2d_shape_basic_Oval") {

        TakeOffLite.startProcess('DeleteSheetObject');
        $.ajax({
            url: 'Projects.asmx/DeleteSheetObjec', // TODO: typo?
            data: {
                Client: projectVars.userId,
                ObjectID: Id,
                SheetID: urlVars.sh
            },
            async: false,
            method: 'post',
            dataType: 'xml',
            success: function (data) {
                TakeOffLite.endProcess('DeleteSheetObject');
                $('#liSheetObject' + $(data).find('SheetObjectID').text()).remove();
                $('#TakeoffliSheetObject' + $(data).find('SheetObjectID').text()).remove();
                HideLi('liTakeoffs' + urlVars.id);
            },
            error: errorHandlerMisc
        });
    } else {

        TakeOffLite.startProcess('DeleteSheetPoint');
        $.ajax({
            url: 'Projects.asmx/DeleteSheetPoint',
            data: {
                Client: projectVars.userId,
                ObjectID: Id,
                SheetID: urlVars.sh
            },
            async: false,
            method: 'post',
            dataType: 'xml',
            success: function (data) {

                TakeOffLite.endProcess('DeleteSheetPoint');

                if ($(data).find('Count').text() > 0) {
                    $('#lbPoint' + $(data).find('N').text()).text("Point :" + $(data).find('Count').text());
                    $('#TakeofflbPoint' + $(data).find('N').text()).text("Point :" + $(data).find('Count').text());
                } else {
                    $('#liSheetPoint' + obj.userData).remove();
                    $('#TakeoffliSheetPoint' + obj.userData).remove();
                    HideLi(urlVars.id);
                }
            },
            error: errorHandlerMisc
        });
    }

}


/** duplicate code removed **/

function Clear(e) {
    document.getElementById('aLine').style.color = "";
    document.getElementById('aPolyLine').style.color = "";
    document.getElementById('aPolygon').style.color = "";
    document.getElementById('aMove').style.color = "";
    document.getElementById('aScale').style.color = "";
    document.querySelector('svg').style.cursor = "";
    document.getElementById('gfx_holder').style.cursor = "";
    document.getElementById('aPoint').style.color = "";
    svg.onmousedown = null;
    // svg.contextmenu= null;
    // svg.oncontextmenu= null;
    e.preventDefault();
    svg.onmousemove = null;
    document.getElementById("gfx_holder").onmousedown = null;
    document.getElementById("gfx_holder").onmousemove = null;
    document.getElementById("gfx_holder").onmouseup = null;
    $('#sweeties').css({"color": "  white"});
    $('#ulScale').css({"display": " none"});
    showScaleMenu = true;
    svg.onmouseup = null;
    Polygon = true;
    Line = true;
    PolyLine = true;
    Point = true;
}

function ClearBoot() {
    document.getElementById('aLine').style.color = "";
    document.getElementById('aPolyLine').style.color = "";
    document.getElementById('aPolygon').style.color = "";
    document.getElementById('aMove').style.color = "";
    document.getElementById('aScale').style.color = "";
    document.querySelector('svg').style.cursor = "";
    document.getElementById('gfx_holder').style.cursor = "";
    document.getElementById('aPoint').style.color = "";
    svg.onmousedown = null;
    document.getElementById("gfx_holder").onmousedown = null;
    document.getElementById("gfx_holder").onmousemove = null;
    document.getElementById("gfx_holder").onmouseup = null;
    svg.onmousemove = null;
    $('#sweeties').css({"color": "  white"});
    $('#ulScale').css({"display": " none"});
    showScaleMenu = true;
    svg.onmouseup = null;
    Polygon = true;
    Line = true;
    PolyLine = true;
    Point = true;
}


var designHasChangedSinceUpdate = false;

/**
 * Drawing design has changed.  Invalidate caches
 * @returns {undefined}
 */
function onDrawingDataChanged(){

    activeObjectInfoId = 'design has changed';

    designHasChangedSinceUpdate = true;

    trace('[onDrawingDataChanged] resetting activeObjectInfoId to ' + activeObjectInfoId);
}

/**
 * save lines
 * @param {type} obj
 * @returns {undefined}
 */
function SaveLines(obj) {

    onDrawingDataChanged();

    console.log('[SaveLines] ');

    //  var Zoom = canvas.getZoom();
    //   var c = $('#gfx_holder');
    //  var  scrollTop=  c.scrollTop();
    //  var scrollLeft= c.scrollLeft();
    //   canvas.setZoom(1.0, false);
    var CanvasLines = obj;
    var scale = 1;
    var figure = canvas.getLines();

    for (var i = 0; i < figure['data'].length; i++) {

        if (figure['data'][i]["id"] == "FirstLine") {
            scale = $('#txtFeet').val() / figure['data'][i].getLength()
            if (isFirstLine2Point = false) {
                figure['data'][i].setVisible(false);
            }

        }

    }

    var Length = (CanvasLines.getLength() * scale).toFixed(2)
    var Points = CanvasLines['vertices']['data'].length;
    var Area = 0;

    //  for (var i=0;i< CanvasLines['data'].length;i++)
    //{
    var vertices = undefined;
    for (var a = 0; a < CanvasLines['vertices']['data'].length; a++) {
        if (vertices != undefined) {
            vertices = vertices + "," + CanvasLines['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasLines['vertices']['data'][a]['y'].toFixed(2);
        } else {
            vertices = CanvasLines['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasLines['vertices']['data'][a]['y'].toFixed(2);

        }
    }

    var requestData = {
        Client: projectVars.userId,
        ProjectID: projectVars.id,
        SheetID: urlVars.sh,
        ObjectID: CanvasLines['id'],
        Vertices: " " + vertices.toString(),
        Type: CanvasLines['cssClass'],
        minX: " ",
        minY: " ",
        maxX: " ",
        maxY: " ",
        width: " " + CanvasLines['width'],
        height: " " + CanvasLines['height'],
        x: " " + CanvasLines['x'],
        y: " " + CanvasLines['y'],
        alpha: CanvasLines['alpha'],
        color: "#" + componentToHex(CanvasLines['lineColor']['red']) + componentToHex(CanvasLines['lineColor']['green']) + componentToHex(CanvasLines['lineColor']['blue']),
        bgColor: "#" + componentToHex(CanvasLines['lineColor']['red']) + componentToHex(CanvasLines['lineColor']['green']) + componentToHex(CanvasLines['lineColor']['blue']),
        start: CanvasLines['start']['x'] + "," + CanvasLines['start']['y'],
        end: CanvasLines['end']['x'] + "," + CanvasLines['end']['y'],
        transform: 0,
        Perimeter: Length,
        Area: Area,
        Points: Points,
        Length: Length
    };

    TakeOffLite.startProcess('SaveSheetFigures');
    $.ajax({
        url: 'Projects.asmx/SaveSheetFigures',
        data: requestData,
        async: false,
        method: 'post',
        dataType: 'xml',
        success: onSaveFiguresSuccess,
        myCanvasLines: CanvasLines,
        error: errorHandlerMisc
    });

    //}
    //  canvas.setZoom(Zoom, false);

    //   c.scrollTop(scrollTop);
    //   c.scrollLeft(scrollLeft);
}

/**
 * success handler for ajax call to save drawing data
 * @param {type} data
 * @returns {undefined}
 */
function onSaveFiguresSuccess(data) {

    console.log('[onSaveFiguresSuccess] ', data);

    TakeOffLite.endProcess('SaveSheetFigures');

    var CanvasLines = this.myCanvasLines;

    var notFirstLine = CanvasLines['id'] != "FirstLine";
    if (notFirstLine) {
        if ($(data).find('New').text() == 0) {
            $('#lbPerimeter' + $(data).find('SheetObjectID').text()).text(" P: " + data.Length);
            $('#TakeofflbPerimeter' + $(data).find('SheetObjectID').text()).text(" P: " + data.Length);
        } else {
            var icon;

            if (CanvasLines['cssClass'] == "draw2d_shape_basic_Line") {
                icon = "   <i  class='fas fa-slash'   ></i> ";
            }
            if (CanvasLines['cssClass'] == "draw2d_shape_basic_PolyLine") {
                icon = "   <i  class='fas fa-project-diagram'   ></i> ";
            }

            // $('#liTakeoffs'+urlVars.id).show();
            $('#ulSheetTakeoffs' + urlVars.id).append("</br><li style=' list-style-type: none;  border-bottom: solid 1px grey;' "
                + " id='liSheetObject" + $(data).find('SheetObjectID').text() + "' > "
                + " <img id='img" + $(data).find('SheetObjectID').text() + "'  src='images/on.png'   onclick='ShowHideFigure(" + $(data).find('SheetObjectID').text() + ")'/>     &nbsp;&nbsp; " + icon
                + " &nbsp;&nbsp; <i  class='fas' style='width: 16px; height: 16px; background-color:" + "#" + componentToHex(CanvasLines['lineColor']['red']) + componentToHex(CanvasLines['lineColor']['green']) + componentToHex(CanvasLines['lineColor']['blue']) + "; ' ></i> "
                + "<a href='Projects.aspx?id=" + urlVars.id + "&sh=" + urlVars.sh + "%>'> <i   style='-webkit-font-smoothing: antialiased;       display: inline-block;          font-style: normal;           font-variant: normal;"
                + " text-rendering: auto;   line-height: 1; width:20%;'  id='iSheetObjectID" + $(data).find('SheetObjectID').text() + "'  title='" + CanvasLines['id'] + "' >" + CanvasLines['id'] + "</i> </a>  "
                + " <label  ID='lbPerimeter" + $(data).find('SheetObjectID').text() + "'   >  P: " + data.Length + "</label>                  <label  ID='lbArea" + $(data).find('SheetObjectID').text() + "'></label>  </li>  ");


            $('#TakeoffulSheet' + urlVars.id).append("</br><li style=' list-style-type: none;  border-bottom: solid 1px grey;'"
                + " id='TakeoffliSheetObject" + $(data).find('SheetObjectID').text() + "' >  &nbsp;&nbsp; " + icon + "    &nbsp;&nbsp; <i  class='fas'    style='  width: 16px;     height: 16px; background-color:" + "#" + componentToHex(CanvasLines['lineColor']['red']) + componentToHex(CanvasLines['lineColor']['green']) + componentToHex(CanvasLines['lineColor']['blue']) + "; ' ></i> "
                + " <a href='Projects.aspx?id=" + urlVars.id + "&sh=" + urlVars.sh + "'>  <i   style='-webkit-font-smoothing: antialiased;       display: inline-block;          font-style: normal;           font-variant: normal;"
                + "  text-rendering: auto;   line-height: 1; width:40%;'  id='TakeoffiSheetObjectID" + $(data).find('SheetObjectID').text() + "'  title='" + CanvasLines['id'] + "' >" + CanvasLines['id'] + "</i></a>   "
                + "  <label  ID='TakeofflbPerimeter" + $(data).find('SheetObjectID').text() + "'   >  P: " + data.Length + "</label>                  <label  ID='TakeofflbArea" + $(data).find('SheetObjectID').text() + "'></label>  </li>  ");
            HideLi('liTakeoffs' + urlVars.id);
            CheckSheetObjectID('iSheetObjectID' + $(data).find('SheetObjectID').text());
            CheckSheetObjectID('TakeoffiSheetObjectID' + $(data).find('SheetObjectID').text());
        }
        ;
    }
    ;
}

/**
 * Add item to SS
 * @param {type} ObjID
 * @returns {undefined}
 */
function AddItemToSS(ObjID) {

    TakeOffLite.startProcess('AddItemToSS');
    $.ajax({
        url: 'Projects.asmx/AddItemToSS',
        data: {
            Client: projectVars.userId,
            ProjectID: projectVars.id,
            SheetID: urlVars.sh,
            ObjectID: ObjID,
            ItemsID: ItemsID

        },
        async: false,
        method: 'post',
        dataType: 'xml',
        success: function (data) {
            TakeOffLite.endProcess('AddItemToSS');
            alert(' Item ' + sNameItems + '   with ' + ObjID + ' measurement add to Spreadsheet');
            ItemsAdd = false;
            document.querySelector('svg').style.cursor = "default";

            $("#Group" + ItemsID).css("background-color", "");
        },
        error:
            function (data) {
                //       alert('ошибка');
            }
    });
}


function SaveSheetFigures() {

    console.log('[SaveSheetFigures]');

    canvas.setZoom(1.0, false);
    var CanvasFigures = canvas.getFigures();
    for (var i = 0; i < CanvasFigures['data'].length; i++) {
        if (CanvasFigures['data'][i]['cssClass'] != "draw2d_shape_basic_Oval") {

            var vertices = undefined;
            for (var a = 0; a < CanvasFigures['data'][i]['vertices']['data'].length; a++) {
                if (vertices != undefined) {
                    vertices = vertices + "," + CanvasFigures['data'][i]['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasFigures['data'][i]['vertices']['data'][a]['y'].toFixed(2);
                } else {
                    vertices = CanvasFigures['data'][i]['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasFigures['data'][i]['vertices']['data'][a]['y'].toFixed(2);

                }
            }

            $.ajax({
                url: 'Projects.asmx/SaveSheetFiguresTransform',
                data: {
                    Client: projectVars.userId,
                    ProjectID: projectVars.id,
                    SheetID: urlVars.sh,
                    ObjectID: CanvasFigures['data'][i]['id'],
                    Vertices: " " + vertices.toString(),
                    Type: CanvasFigures['data'][i]['cssClass'],
                    minX: " " + CanvasFigures['data'][i]['minX'].toFixed(2),
                    minY: " " + CanvasFigures['data'][i]['minY'].toFixed(2),
                    maxX: " " + CanvasFigures['data'][i]['maxX'].toFixed(2),
                    maxY: " " + CanvasFigures['data'][i]['maxY'].toFixed(2),
                    width: " " + CanvasFigures['data'][i]['width'],
                    height: " " + CanvasFigures['data'][i]['height'],
                    x: " " + CanvasFigures['data'][i]['x'].toFixed(2),
                    y: " " + CanvasFigures['data'][i]['y'].toFixed(2),
                    alpha: CanvasFigures['data'][i]['alpha'],
                    color: "#" + componentToHex(CanvasFigures['data'][i]['color']['red']) + componentToHex(CanvasFigures['data'][i]['color']['green']) + componentToHex(CanvasFigures['data'][i]['color']['blue']),
                    bgColor: "#" + componentToHex(CanvasFigures['data'][i]['bgColor']['red']) + componentToHex(CanvasFigures['data'][i]['bgColor']['green']) + componentToHex(CanvasFigures['data'][i]['bgColor']['blue']),
                    start: " ",
                    end: " ",
                    transform: 0

                },
                async: false,
                method: 'post',
                dataType: 'xml',
                success: function (data) {
                    //        alert('сохранилось');
                },
                error:
                    function (data) {
                        //       alert('ошибка');
                    }
            });

        } else {

            console.log('[SaveSheetFigures]');

            $.ajax({
                url: 'Projects.asmx/SaveSheetFiguresTransform',
                data: {
                    Client: projectVars.userId,
                    ProjectID: projectVars.id,
                    SheetID: urlVars.sh,
                    ObjectID: CanvasFigures['data'][i]['id'],
                    Vertices: " ",
                    Type: CanvasFigures['data'][i]['cssClass'],
                    minX: " ",
                    minY: " ",
                    maxX: " ",
                    maxY: " ",
                    width: " " + CanvasFigures['data'][i]['width'],
                    height: " " + CanvasFigures['data'][i]['height'],
                    x: " " + CanvasFigures['data'][i]['x'].toFixed(2),
                    y: " " + CanvasFigures['data'][i]['y'].toFixed(2),
                    alpha: CanvasFigures['data'][i]['alpha'],
                    color: "#" + componentToHex(CanvasFigures['data'][i]['color']['red']) + componentToHex(CanvasFigures['data'][i]['color']['green']) + componentToHex(CanvasFigures['data'][i]['color']['blue']),
                    bgColor: "#" + componentToHex(CanvasFigures['data'][i]['bgColor']['red']) + componentToHex(CanvasFigures['data'][i]['bgColor']['green']) + componentToHex(CanvasFigures['data'][i]['bgColor']['blue']),
                    start: " ",
                    end: " ",
                    transform: 0

                },
                async: false,
                method: 'post',
                dataType: 'xml',
                success: function (data) {
                    //        alert('сохранилось');
                },
                error:
                    function (data) {
                        //       alert('ошибка');
                    }
            });

        }
    }
}


function SavePolygon(obj) {

    var Zoom = canvas.getZoom();
    var c = $('#gfx_holder');

    var scrollTop = c.scrollTop();
    var scrollLeft = c.scrollLeft();
    canvas.setZoom(1.0, false);
    var CanvasFigures = obj;



    var scale = 1;
    var divCont = document.getElementById("divCont");
    var Points = CanvasFigures.vertices.getSize();


    var figure = canvas.getLines();
    var Area = 0
    var LineeHide = []
    var FigureHide = []

    for (var i = 0; i < figure['data'].length; i++) {

        if (figure['data'][i]["id"] == "FirstLine") {
            scale = $('#txtFeet').val() / figure['data'][i].getLength();
        }

        LineeHide[i] = figure['data'][i]["visible"];
        figure['data'][i].setVisible(false);
    }

    var Length = CanvasFigures.getLength() * scale;

    var figure = canvas.getFigures();

    for (var i = 0; i < figure['data'].length; i++) {
        FigureHide[i] = figure['data'][i]["visible"];

        if (figure['data'][i]["id"] != obj.id) {
            figure['data'][i].setVisible(false);
        }
    }


    var sheight = document.querySelector('svg').getAttribute('height');
    var swidth	= document.querySelector('svg').getAttribute('width');
    var Pixel = 0;

    var canvas1 = document.getElementById('CanTest');
    canvas1.height = sheight;
    canvas1.width = swidth;

    var ctx = canvas1.getContext("2d");
    ctx.clearRect(0, 0, swidth, sheight);
    var svgString = new XMLSerializer().serializeToString(document.querySelector('svg'));


    //   CanvasFigures.setSelectable(false);

    var DOMURL = self.URL || self.webkitURL || self;
    var img = new Image();
    var svg = new Blob([svgString], {type: "image/svg+xml;charset=utf-8"});
    //   var svg = new Blob(svg, {type: "image/xml;charset=utf-8"});
    var url = DOMURL.createObjectURL(svg);
    img.onload = function () {
        ctx.drawImage(img, 0, 0);

        var imgData = ctx.getImageData(0, 0, canvas1.width, canvas1.height);

        var point = 0;

        for (var i = 0; i < imgData.data.length; i += 4) {
            if (imgData.data[i] != 0) {
                var Color1 = imgData.data[i];
                var Color2 = imgData.data[i + 1];
                var Color3 = imgData.data[i + 2];
                var test4 = imgData.data[i + 3];



                /*if ((Color1 == CanvasFigures['color']['red']) && (Color2 ==CanvasFigures['color']['green']) && (Color3 == CanvasFigures['color']['blue'])) {


                 Pixel++;
                 }*/
                if ((Color1 == 2) && (Color2 == 136) && (Color3 == 209)) {


                    point++;
                }
                if ((Color1 == 91) && (Color2 == 202) && (Color3 == 255)) {


                    point++;
                }

                if ((Color1 != 00) && (Color2 != 00) && (Color3 != 00)) {


                    Pixel++;
                }
            }

        }
        Pixel = Pixel - (point * 2.4);
        var scalesquared = (scale * scale);

        Area = Pixel * scalesquared;

        {
            var vertices = undefined;
            for (var a = 0; a < CanvasFigures['vertices']['data'].length; a++) {
                if (vertices != undefined) {
                    vertices = vertices + "," + CanvasFigures['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasFigures['vertices']['data'][a]['y'].toFixed(2);
                } else {
                    vertices = CanvasFigures['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasFigures['vertices']['data'][a]['y'].toFixed(2);

                }
            }

            TakeOffLite.startProcess('SaveSheetFigures');
            $.ajax({
                url: 'Projects.asmx/SaveSheetFigures',
                data: {
                    Client: projectVars.userId,
                    ProjectID: projectVars.id,
                    SheetID: urlVars.sh,
                    ObjectID: CanvasFigures['id'],
                    Vertices: " " + vertices.toString(),
                    Type: CanvasFigures['cssClass'],
                    minX: " " + CanvasFigures['minX'].toFixed(2),
                    minY: " " + CanvasFigures['minY'].toFixed(2),
                    maxX: " " + CanvasFigures['maxX'].toFixed(2),
                    maxY: " " + CanvasFigures['maxY'].toFixed(2),
                    width: " " + CanvasFigures['width'].toFixed(2),
                    height: " " + CanvasFigures['height'].toFixed(2),
                    x: " " + CanvasFigures['x'].toFixed(2),
                    y: " " + CanvasFigures['y'].toFixed(2),
                    alpha: CanvasFigures['alpha'],
                    color: "#" + componentToHex(CanvasFigures['color']['red']) + componentToHex(CanvasFigures['color']['green']) + componentToHex(CanvasFigures['color']['blue']),
                    bgColor: "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']),
                    start: " ",
                    end: " ",
                    transform: 0,
                    Perimeter: Length.toFixed(2),
                    Area: Area.toFixed(2),
                    Points: Points

                },
                async: false,
                method: 'post',
                dataType: 'xml',
                success: function (data) {

                    TakeOffLite.endProcess('SaveSheetFigures');

                    if ($(data).find('New').text() == 0) {

                        $('#lbPerimeter' + $(data).find('SheetObjectID').text()).text("  P: " + Length.toFixed(2));
                        $('#lbArea' + $(data).find('SheetObjectID').text()).text("A- " + Area.toFixed(2));
                        $('#TakeofflbPerimeter' + $(data).find('SheetObjectID').text()).text("  P: " + Length.toFixed(2));
                        $('#TakeofflbArea' + $(data).find('SheetObjectID').text()).text("A-" + Area.toFixed(2));

                    } else {

                        $('#ulSheetTakeoffs' + urlVars.id).append("<br><li style=' list-style-type: none;  border-bottom: solid 1px grey;'   id='liSheetObject" + $(data).find('SheetObjectID').text() + "' >  <img id='img" + $(data).find('SheetObjectID').text() + "'  src='images/on.png'   onclick='ShowHideFigure(" + $(data).find('SheetObjectID').text() + ")' />  &nbsp;&nbsp;  <i  class='fas fa-vector-square'   ></i>  &nbsp;&nbsp; <i  class='fas'    style='  width: 16px;     height: 16px; background-color:" + "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']) + "; ' ></i> <a href='Projects.aspx?id='" + urlVars.id + "&sh=" + urlVars.sh + ">   <i   style='-webkit-font-smoothing: antialiased;       display: inline-block;          font-style: normal;           font-variant: normal;" +
                            "  text-rendering: auto;   line-height: 1; width:20%;' id='iSheetObjectID" + $(data).find('SheetObjectID').text() + "'   title='" + CanvasFigures['id'] + "' >" + CanvasFigures['id'] + "</i> </a>   " +
                            "  <label  ID='lbPerimeter" + $(data).find('SheetObjectID').text() + "'   >  P: " + Length.toFixed(2) + "</label>               <label  ID='lbArea" + $(data).find('SheetObjectID').text() + "'>A-" + Area.toFixed(2) + "</label>  </li>  ");
                        HideLi('liTakeoffs' + urlVars.id);


                        $('#TakeoffulSheet' + urlVars.id).append("<br><li style=' list-style-type: none;  border-bottom: solid 1px grey;'   id='TakeoffliSheetObject" + $(data).find('SheetObjectID').text() + "' >&nbsp;&nbsp;  <i  class='fas fa-vector-square'   ></i>    &nbsp;&nbsp; <i  class='fas'    style='  width: 16px;     height: 16px; background-color:" + "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']) + "; ' ></i> <a href='Projects.aspx?id=" + urlVars.id + "&sh=" + urlVars.sh + "'>   <i   style='-webkit-font-smoothing: antialiased;       display: inline-block;          font-style: normal;           font-variant: normal;" +
                            "  text-rendering: auto;   line-height: 1; width:20%;' id='TakeoffiSheetObjectID" + $(data).find('SheetObjectID').text() + "'   title='" + CanvasFigures['id'] + "' >" + CanvasFigures['id'] + "</i> </a>   " +
                            "  <label  ID='TakeofflbPerimeter" + $(data).find('SheetObjectID').text() + "'   >  P: " + Length.toFixed(2) + "</label>                <label  ID='TakeofflbArea" + $(data).find('SheetObjectID').text() + "'>A-" + Area.toFixed(2) + "</label>  </li>  ");


                        CheckSheetObjectID('iSheetObjectID' + $(data).find('SheetObjectID').text());
                        CheckSheetObjectID('TakeoffiSheetObjectID' + $(data).find('SheetObjectID').text());
                    }

                },
                error: errorHandlerMisc
            });

        }
        //  CanvasFigures.setSelectable(true);
        ctx.clearRect(0, 0, swidth, sheight);
    };
    img.src = url;



    var figure = canvas.getLines();

    for (var i = 0; i < figure['data'].length; i++) {
        if (LineeHide[i] == true) {

            if (figure['data'][i]['id'] != 'FirstLine') {
                figure['data'][i].setVisible(true);
            }
        }
    }

    var figure = canvas.getFigures();

    for (var i = 0; i < figure['data'].length; i++) {
        if (FigureHide[i] == true) {
            figure['data'][i].setVisible(true);
        }
    }


    canvas.setZoom(Zoom, false);

    c.scrollTop(scrollTop);
    c.scrollLeft(scrollLeft);




}


function SavePoint(obj) {

    var CanvasFigures = obj;

    TakeOffLite.startProcess('SaveSheetFiguresMove');
    $.ajax({
        url: 'Projects.asmx/SaveSheetFiguresMove',
        data: {
            Client: projectVars.userId,
            ProjectID: projectVars.id,
            SheetID: urlVars.sh,
            ObjectID: CanvasFigures['id'],
            Vertices: " ",
            Type: CanvasFigures['cssClass'],
            minX: " ",
            minY: " ",
            maxX: " ",
            maxY: " ",
            width: " " + CanvasFigures['width'],
            height: " " + CanvasFigures['height'],
            x: " " + CanvasFigures['x'].toFixed(2),
            y: " " + CanvasFigures['y'].toFixed(2),
            alpha: CanvasFigures['alpha'],
            color: "#" + componentToHex(CanvasFigures['color']['red']) + componentToHex(CanvasFigures['color']['green']) + componentToHex(CanvasFigures['color']['blue']),
            bgColor: "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']),
            start: " ",
            end: " ",
            transform: 0

        },
        async: false,
        method: 'post',
        dataType: 'xml',
        success: function (data) {
            TakeOffLite.endProcess('SaveSheetFiguresMove');
        },
        error: errorHandlerMisc
    });
}




function AddNewPoint(obj) {

    var CanvasFigures = obj;



    TakeOffLite.startProcess('AddSheetPoint');
    $.ajax({
        url: 'Projects.asmx/AddSheetPoint',
        data: {
            Client: projectVars.userId,
            ProjectID: projectVars.id,
            SheetID: urlVars.sh,
            ObjectID: CanvasFigures['id'],

            Name: $('#txtNameObj').val(),
            Type: CanvasFigures['cssClass'],

            width: " " + CanvasFigures['width'],
            height: " " + CanvasFigures['height'],
            x: " " + CanvasFigures['x'].toFixed(2),
            y: " " + CanvasFigures['y'].toFixed(2),
            alpha: CanvasFigures['alpha'],
            color: "#" + componentToHex(CanvasFigures['color']['red']) + componentToHex(CanvasFigures['color']['green']) + componentToHex(CanvasFigures['color']['blue']),
            bgColor: "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']),
            Group: GroupPoint

        },
        async: false,
        method: 'post',
        dataType: 'xml',
        success: function (data) {

            TakeOffLite.endProcess('AddSheetPoint');

            if ($(data).find('Count').text() > 1) {

                $('#lbPoint' + $(data).find('N').text()).text("Point :" + $(data).find('Count').text());
                $('#TakeofflbPoint' + $(data).find('N').text()).text("Point :" + $(data).find('Count').text());

                obj.userData = $(data).find('N').text();
            } else {

                obj.userData = $(data).find('N').text();

                $('#ulSheetTakeoffs' + urlVars.id).append("<br><li style=' list-style-type: none;  border-bottom: solid 1px grey;'   id='liSheetPoint" + $(data).find('N').text() + "' > <a href='Projects.aspx?id='" + urlVars.id + "&sh=" + urlVars.sh + ">   <i   style='-webkit-font-smoothing: antialiased;       display: inline-block;          font-style: normal;           font-variant: normal;" +
                    "  text-rendering: auto;   line-height: 1; width:40%;' id='iSheetPoint" + $(data).find('Group').text() + "'   title='" + $('#txtNameObj').val() + "' >" + $('#txtNameObj').val() + "</i> <a>  &nbsp;&nbsp;  <i  class='fas fa-vector-square'   ></i>   &nbsp;&nbsp; <i  class='fas'    style='  width: 16px;     height: 16px; background-color:" + "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']) + "; ' ></i> <br> " +
                    "  <label  ID='lbPoint" + $(data).find('N').text() + "'> Point : " + $(data).find('Count').text() + "</label> </li>  ");
                HideLi('liTakeoffs' + urlVars.id);

                $('#TakeoffulSheet' + urlVars.id).append("<br><li style=' list-style-type: none;  border-bottom: solid 1px grey;'   id='TakeoffliSheetPoint" + $(data).find('N').text() + "' > <a href='Projects.aspx?id='" + urlVars.id + "&sh=" + urlVars.sh + ">   <i   style='-webkit-font-smoothing: antialiased;       display: inline-block;          font-style: normal;           font-variant: normal;" +
                    "  text-rendering: auto;   line-height: 1; width:40%;' id='TakeoffiSheetPoint" + $(data).find('Group').text() + "'   title='" + $('#txtNameObj').val() + "' >" + $('#txtNameObj').val() + "</i> <a>  &nbsp;&nbsp;  <i  class='fas fa-vector-square'   ></i>   &nbsp;&nbsp; <i  class='fas'    style='  width: 16px;     height: 16px; background-color:" + "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']) + "; ' ></i> <br> " +
                    "  <label  ID='TakeofflbPoint" + $(data).find('N').text() + "'> Point : " + $(data).find('Count').text() + "</label> </li>  ");

                CheckSheetObjectID('iSheetPoint' + $(data).find('N').text());
                CheckSheetObjectID('TakeoffiSheetPoint' + $(data).find('N').text());
            }
        },
        error: errorHandlerMisc
    });
}



/**
 * Save a polygon move
 * @param {type} obj
 * @returns {undefined}
 */
function SavePolygonMove(obj) {

    var CanvasFigures = obj;

    var vertices = undefined;
    for (var a = 0; a < CanvasFigures['vertices']['data'].length; a++) {
        if (vertices != undefined) {
            vertices = vertices + "," + CanvasFigures['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasFigures['vertices']['data'][a]['y'].toFixed(2);
        } else {
            vertices = CanvasFigures['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasFigures['vertices']['data'][a]['y'].toFixed(2);

        }
    }

    TakeOffLite.startProcess('SaveSheetFiguresMove');
    $.ajax({
        url: 'Projects.asmx/SaveSheetFiguresMove',
        data: {
            Client: projectVars.userId,
            ProjectID: projectVars.id,
            SheetID: urlVars.sh,
            ObjectID: CanvasFigures['id'],
            Vertices: " " + vertices.toString(),
            Type: CanvasFigures['cssClass'],
            minX: " " + CanvasFigures['minX'].toFixed(2),
            minY: " " + CanvasFigures['minY'].toFixed(2),
            maxX: " " + CanvasFigures['maxX'].toFixed(2),
            maxY: " " + CanvasFigures['maxY'].toFixed(2),
            width: " " + CanvasFigures['width'],
            height: " " + CanvasFigures['height'],
            x: " " + CanvasFigures['x'].toFixed(2),
            y: " " + CanvasFigures['y'].toFixed(2),
            alpha: CanvasFigures['alpha'],
            color: "#" + componentToHex(CanvasFigures['color']['red']) + componentToHex(CanvasFigures['color']['green']) + componentToHex(CanvasFigures['color']['blue']),
            bgColor: "#" + componentToHex(CanvasFigures['bgColor']['red']) + componentToHex(CanvasFigures['bgColor']['green']) + componentToHex(CanvasFigures['bgColor']['blue']),
            start: " ",
            end: " ",
            transform: 0

        },
        async: false,
        method: 'post',
        dataType: 'xml',
        success: function (data) {
            TakeOffLite.endProcess('SaveSheetFiguresMove');
        },
        error: errorHandlerMisc
    });
}

/**
 * Save sheet lines
 * @returns {undefined}
 */
function SaveSheetLines() {

    canvas.setZoom(1.0, false);

    var CanvasLines = canvas.getLines();

    for (var i = 0; i < CanvasLines['data'].length; i++) {
        var vertices = undefined;
        for (var a = 0; a < CanvasLines['data'][i]['vertices']['data'].length; a++) {
            if (vertices != undefined) {
                vertices = vertices + "," + CanvasLines['data'][i]['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasLines['data'][i]['vertices']['data'][a]['y'].toFixed(2);
            } else {
                vertices = CanvasLines['data'][i]['vertices']['data'][a]['x'].toFixed(2) + "," + CanvasLines['data'][i]['vertices']['data'][a]['y'].toFixed(2);

            }
        }

        TakeOffLite.startProcess('SaveSheetFiguresTransform');
        $.ajax({
            url: 'Projects.asmx/SaveSheetFiguresTransform',
            data: {
                Client: projectVars.userId,
                ProjectID: projectVars.id,
                SheetID: urlVars.sh,
                ObjectID: CanvasLines['data'][i]['id'],
                Vertices: " " + vertices.toString(),
                Type: CanvasLines['data'][i]['cssClass'],
                minX: " ",
                minY: " ",
                maxX: " ",
                maxY: " ",
                width: " " + CanvasLines['data'][i]['width'],
                height: " " + CanvasLines['data'][i]['height'],
                x: " " + CanvasLines['data'][i]['x'].toFixed(2),
                y: " " + CanvasLines['data'][i]['y'].toFixed(2),
                alpha: CanvasLines['data'][i]['alpha'],
                color: "#" + componentToHex(CanvasLines['data'][i]['lineColor']['red']) + componentToHex(CanvasLines['data'][i]['lineColor']['green']) + componentToHex(CanvasLines['data'][i]['lineColor']['blue']),
                bgColor: "#" + componentToHex(CanvasLines['data'][i]['lineColor']['red']) + componentToHex(CanvasLines['data'][i]['lineColor']['green']) + componentToHex(CanvasLines['data'][i]['lineColor']['blue']),
                start: CanvasLines['data'][i]['start']['x'].toFixed(2) + "," + CanvasLines['data'][i]['start']['y'].toFixed(2),
                end: CanvasLines['data'][i]['end']['x'].toFixed(2) + "," + CanvasLines['data'][i]['end']['y'].toFixed(2),

                transform: 0

            },
            async: false,
            method: 'post',
            dataType: 'xml',
            success: function (data) {
                TakeOffLite.endProcess('SaveSheetFiguresTransform');
            },
            error: errorHandlerMisc
        });
    }
}

/**
 * Color component To Hex equivalent
 * @param {type} c
 * @returns {componentToHex.hex|String}
 */
function componentToHex(c) {
    var hex = c.toString(16);
    return hex.length == 1 ? "0" + hex : hex;
}

/**
 * get sheet object from server
 * @returns {Array}
 */
function GetSheetObject(__usePageData) {

    //
    var jsonDocument1 = [];

    // spinner
    TakeOffLite.startProcess('GetSheetObject');

    console.log('[GetSheetObject] Getting sheet object.  Client ID: ', projectVars);

    // New: It looks like the sheet object is already dropped to page by projects.aspx in projectData.sheetObject

    var hasSheetObjOnPage = !isUndefined(projectData) && !isUndefined(projectData.sheetObject);
    var usePageData = false;// (__usePageData === true) && hasSheetObjOnPage;
    if( usePageData ){
        //TODO: eliminate this server call by using the sheet object on page.
        trace('Using Sheet Object on page: ', projectVars.sheetObject);
        jsonDocument1 = eval( String(projectVars.sheetObject) );
        TakeOffLite.endProcess('GetSheetObject');
    } else {
        // server calls R us
        $.ajax({
            url: 'Projects.asmx/GetSheetObject',
            data: {
                Client:		projectVars.userId,
                ProjectID:	projectVars.id,
                SheetID:	urlVars.sh,
            },
            async: false,
            method: 'post',
            dataType: 'xml',
            success: function(msg){
                jsonDocument1 = parseSheetObject(msg);
                TakeOffLite.endProcess('GetSheetObject');
            },
            error: errorHandlerMisc
        });
    }

    return jsonDocument1;
}

/**
 * parse the sheet object data - this is an event handler from getSheetObject()
 * @param {type} msg
 * @returns {undefined}
 */
function parseSheetObject(msg) {

    console.log('[parseSheetObject] Got Sheet Object: ', msg);

    var jsonDocument1 = [];

    // parse each table.  each table is a drawing object from what i can tell
    $(msg).find('Table').each(function (i, row) {

        var objType = $(row).find('Type').text();

        console.log('[parseSheetObject] Parsing Object from type: ' + objType, [  ] );

        // find obj type
        var isSomeKindaLine = (objType === 'draw2d.shape.basic.Line')
            || ($(row).find('Type').text() === 'draw2d.shape.basic.PolyLine');
        var isSVG			= $(row).find('ObjectID').text() == 'SVG';
        var isOval			= ($(row).find('Type').text() == 'draw2d.shape.basic.Oval');
        var isPolygon		= $(row).find('Type').text() == 'draw2d.shape.basic.Polygon';// polygon

        // create each type as JSON

        // polygon
        if (isPolygon) {
            var XY = $(row).find('Vertices').text().split(',');
            var Vertices = [];
            var s = 0;
            for (var a = 0; a < XY.length; ) {
                Vertices[s] = {x: XY[a], y: XY[a + 1]};
                s++;
                a = a + 2;
            }
            jsonDocument1[i] =
                {
                    "type": $(row).find('Type').text(),
                    "id":	$(row).find('ObjectID').text(),
                    "x":	$(row).find('x').text(),
                    "y":	$(row).find('y').text(),
                    "width": $(row).find('width').text(),
                    "height": $(row).find('height').text(),
                    "color": $(row).find('color').text(),
                    "bgColor": $(row).find('bgColor').text(),
                    "vertices": Vertices,
                    "minX": $(row).find('minX').text(),
                    "minY": $(row).find('minY').text(),
                    "maxX": $(row).find('maxX').text(),
                    "maxY": $(row).find('maxY').text(),
                    "alpha": $(row).find('alpha').text()
                }
        }

        // is line?
        if (isSomeKindaLine) {

            if ($(row).find('ObjectID').text() == 'FirstLine') {
                doFirstLineUX = false;
            }
            var XY = $(row).find('Vertices').text().split(',')
            var XYstart = $(row).find('Vertices').text().split(',')
            var XYend = $(row).find('Vertices').text().split(',')
            var Vertices = [];

            var s = 0;
            for (var a = 0; a < XY.length; ) {
                Vertices[s] = {x: parseInt(XY[a]), y: parseInt(XY[a + 1])};
                s++;
                a = a + 2;
            }
            jsonDocument1[i] =
                {
                    "type": $(row).find('Type').text(),
                    "id": $(row).find('ObjectID').text(),
                    "x": $(row).find('x').text(),
                    "y": $(row).find('y').text(),
                    "width": $(row).find('width').text(),
                    "height": $(row).find('height').text(),
                    "color": $(row).find('color').text(),
                    "bgColor": $(row).find('bgColor').text(),
                    "vertex": Vertices,
                    "start": {x: XYstart[0], y: XYstart[1]},
                    "end": {x: XYend[0], y: XYend[1]},
                    "alpha": $(row).find('alpha').text(),
                    "minX": $(row).find('minX').text(),
                    "minY": $(row).find('minY').text(),
                    "maxX": $(row).find('maxX').text(),
                    "maxY": $(row).find('maxY').text(),
                    "stroke": 2
                };
        } /* else  ? */

        //NOTE: for svg to exist, draw2d must have initialized before this.

        if (isSVG && !isUndefined(svg)) {

            var hasImgSVG = $('#imgSVG').length > 0;

            trace('[parseSheetObject] Parsing SVG / Inserting Image SVG... hasImgSVG? ' + hasImgSVG);

            HeightDefault = $(row).find('height').text();
            svg.setAttribute('width', $(row).find('width').text());
            svg.setAttribute('height', $(row).find('height').text());


            //if(hasImgSVG) {
            document.getElementById('imgSVG').setAttribute('width', $(row).find('width').text());
            document.getElementById('imgSVG').setAttribute('height', $(row).find('height').text());
            //}

            // rotations
            if (Rotate === 90)
                itransform = 90;
            else if (Rotate === 0)
                itransform = 180;
            else if (Rotate === -90)
                itransform = 270;

            // transform
            if ($(row).find('transform').text() == "0") {
                svg.setAttribute('height', $('#imgSVG').attr("height"));
                $('#imgSVG').attr("x", "0");
                $('#imgSVG').css("transform-origin", "");
                $('#imgSVG').css("transform", "");

            } else if ($(row).find('transform').text() == "90") {
                $('#imgSVG').attr("x", $('#imgSVG').attr("height") / 2);
                $('#imgSVG').css("transform-origin", "50%  " + $('#imgSVG').attr("height") / 2 + "px");
                $('#imgSVG').css("transform", "rotate(90deg)");
                svg.setAttribute('height', $('#imgSVG').attr("width"));

                //   canvas.setHeight( $('#imgSVG').attr("width"));
                //  alert( canvas.getHeight());

            } else if ($(row).find('transform').text() == "180") {
                $('#imgSVG').attr("x", "0");
                $('#imgSVG').css("transform-origin", "center center");
                $('#imgSVG').css("transform", "rotate(180deg)");
                svg.setAttribute('height', $('#imgSVG').attr("height"));
            } else if ($(row).find('transform').text() == "270") {
                $('#imgSVG').attr("x", -$('#imgSVG').attr("height") / 2);
                $('#imgSVG').css("transform-origin", "50%  " + $('#imgSVG').attr("height") / 2 + "px");
                $('#imgSVG').css("transform", "rotate(270deg)");

                svg.setAttribute('height', $('#imgSVG').attr("width"));
                // canvas.height = $('#imgSVG').attr("width");
            }

        }

        if (isOval) {

            //draw2d.shape.basic.Circle
            jsonDocument1[i] =
                {
                    "type": $(row).find('Type').text(),
                    "id": $(row).find('ObjectID').text(),
                    "x": $(row).find('x').text(),
                    "y": $(row).find('y').text(),
                    "width": $(row).find('width').text(),
                    "height": $(row).find('height').text(),
                    "color": $(row).find('color').text(),
                    "bgColor": $(row).find('bgColor').text(),
                    "alpha": $(row).find('alpha').text(),
                    "userData": $(row).find('group').text()
                };

        }
    });

    return jsonDocument1;
}

/**
 * Save sheet SVG
 * @returns {undefined}
 **/
function SaveSheetSVG() {

    var getHeightSVG = svg.getAttribute('height');
    svg.setAttribute('height', HeightDefault);
    var imgSVG = $('#imgSVG');


    // transform / rotation
    var imgTransform = imgSVG.css("transform");
    if (imgTransform == "none") {
        itransform = 0;
    } else {

        var values = imgSVG.split('(')[1],
            values = values.split(')')[0],
            values = values.split(',');
        var a = values[0];
        var b = values[1];
        var c = values[2];
        var d = values[3];
        Rotate = Math.round(Math.asin(b, a) * (180 / Math.PI));
        if (Rotate === 90)
            itransform = 90;
        else if (Rotate === 0)
            itransform = 180;
        else if (Rotate === -90)
            itransform = 270;
    }

    TakeOffLite.startProcess('SaveSheetFiguresTransform');
    $.ajax({
        url: 'Projects.asmx/SaveSheetFiguresTransform',
        data: {
            Client: projectVars.userId,
            ProjectID: projectVars.id,
            SheetID: urlVars.sh,
            ObjectID: 'SVG',
            Vertices: " ",
            Type: " ",
            minX: " ",
            minY: " ",
            maxX: " ",
            maxY: " ",
            width: " " + svg.getAttribute('width'),
            height: " " + svg.getAttribute('height'),
            x: " ",
            y: " ",
            alpha: " ",
            color: " ",
            bgColor: " ",
            start: " ",
            end: " ",
            transform: itransform
        },
        async: false, /* NOT Async?  this is why its so slow.  There are lots of these, some chained together. */
        method: 'post',
        dataType: 'xml',
        success: function (data) {
            TakeOffLite.endProcess('SaveSheetFiguresTransform');
        },
        error: errorHandlerMisc
    });



    svg.setAttribute('height', getHeightSVG);

}


function DeleteButton() {
    canvas.getCommandStack().startTransaction(draw2d.Configuration.i18n.command.deleteShape);

    canvas.getSelection().each(function (index, figure) {
        var cmd = figure.createCommand(new draw2d.command.CommandType(draw2d.command.CommandType.DELETE));
        if (cmd !== null) {
            canvas.getCommandStack().execute(cmd);
        }
    });
    canvas.getCommandStack().commitTransaction();
}

function UndoB() {
    this.canvas.getCommandStack().undo();

}
function RedoB() {
    this.canvas.getCommandStack().redo();

}


var bTranslate = true;
var Rotate = 1;
function RotateLeft() {
    var imgSVG = $('#imgSVG').css("transform");

    if (imgSVG == "none") {

        $('#imgSVG').attr("x", -$('#imgSVG').attr("height") / 2);
        $('#imgSVG').css("transform-origin", "50%  " + $('#imgSVG').attr("height") / 2 + "px");
        $('#imgSVG').css("transform", "rotate(270deg)");
        /*  $(window).on('load', function() {

         svg.setAttribute('height', $('#imgSVG').attr("width") ) ;
         });*/
        svg.setAttribute('height', $('#imgSVG').attr("width"));
        bTranslate = true;
    } else {



        var values = imgSVG.split('(')[1],
            values = values.split(')')[0],
            values = values.split(',');

        var a = values[0];
        var b = values[1];
        var c = values[2];
        var d = values[3];
        Rotate = Math.round(Math.asin(b, a) * (180 / Math.PI));

        if (Rotate == "-90") {
            $('#imgSVG').attr("x", "0");
            $('#imgSVG').css("transform-origin", "center center");
            $('#imgSVG').css("transform", "rotate(180deg)");
            svg.setAttribute('height', $('#imgSVG').attr("height"));

            bTranslate = false;
        } else if (Rotate == "0") {
            $('#imgSVG').attr("x", $('#imgSVG').attr("height") / 2);
            $('#imgSVG').css("transform-origin", "50%  " + $('#imgSVG').attr("height") / 2 + "px");
            $('#imgSVG').css("transform", "rotate(90deg)");
            svg.setAttribute('height', $('#imgSVG').attr("width"));

            /*  $('#imgSVG').css("transform-origin", "");
             $('#imgSVG').css("transform", "");*/
            bTranslate = true;

        } else if (Rotate == "90") {
            svg.setAttribute('height', $('#imgSVG').attr("height"));
            $('#imgSVG').attr("x", "0");
            $('#imgSVG').css("transform-origin", "");
            $('#imgSVG').css("transform", "");
            bTranslate = false;
        }

    }



    var figure = canvas.getLines(); // canvas.getSelection().getPrimary();







    for (var i = 0; i < figure['data'].length; i++) {
        var rotator = new draw2d.command.CommandRotate(figure['data'][i], -90);

        canvas.getCommandStack().execute(rotator);

    }

    var figure = canvas.getFigures(); // canvas.getSelection().getPrimary();

    for (var i = 0; i < figure['data'].length; i++) {
        var rotator = new draw2d.command.CommandRotate(figure['data'][i], -90);

        canvas.getCommandStack().execute(rotator);

    }

    //   var figure=   canvas.getSelection().getPrimary();
    //   var  rotator=    new  draw2d.command.CommandRotate(figure, -90);

    //   canvas.getCommandStack().execute(rotator)
    // rotate(figure, -90);

    SaveSheetFigures();
    SaveSheetLines();
    SaveSheetSVG();
}




function RotateRight() {


    var imgSVG = $('#imgSVG').css("transform");

    if (imgSVG == "none") {

        $('#imgSVG').attr("x", $('#imgSVG').attr("height") / 2);
        $('#imgSVG').css("transform-origin", "50%  " + $('#imgSVG').attr("height") / 2 + "px");
        $('#imgSVG').css("transform", "rotate(90deg)");
        /*  $(window).on('load', function() {

         svg.setAttribute('height', $('#imgSVG').attr("width") ) ;
         });*/
        svg.setAttribute('height', $('#imgSVG').attr("width"));
        bTranslate = true;
    } else {



        var values = imgSVG.split('(')[1],
            values = values.split(')')[0],
            values = values.split(',');

        var a = values[0];
        var b = values[1];
        var c = values[2];
        var d = values[3];
        Rotate = Math.round(Math.asin(b, a) * (180 / Math.PI));

        if (Rotate == "90") {
            $('#imgSVG').attr("x", "0");
            $('#imgSVG').css("transform-origin", "center center");
            $('#imgSVG').css("transform", "rotate(180deg)");
            svg.setAttribute('height', $('#imgSVG').attr("height"));

            bTranslate = false;
        } else if (Rotate == "0") {
            $('#imgSVG').attr("x", -$('#imgSVG').attr("height") / 2);
            $('#imgSVG').css("transform-origin", "50%  " + $('#imgSVG').attr("height") / 2 + "px");
            $('#imgSVG').css("transform", "rotate(270deg)");
            svg.setAttribute('height', $('#imgSVG').attr("width"));

            /*  $('#imgSVG').css("transform-origin", "");
             $('#imgSVG').css("transform", "");*/
            bTranslate = true;

        } else if (Rotate == "-90") {
            svg.setAttribute('height', $('#imgSVG').attr("height"));
            $('#imgSVG').attr("x", "0");
            $('#imgSVG').css("transform-origin", "");
            $('#imgSVG').css("transform", "");
            bTranslate = false;
        }

    }



    var figure = canvas.getLines(); // canvas.getSelection().getPrimary();







    for (var i = 0; i < figure['data'].length; i++) {
        var rotator = new draw2d.command.CommandRotate(figure['data'][i], 90);

        canvas.getCommandStack().execute(rotator);

    }

    var figure = canvas.getFigures(); // canvas.getSelection().getPrimary();

    for (var i = 0; i < figure['data'].length; i++) {

        var rotator = new draw2d.command.CommandRotate(figure['data'][i], 90);

        canvas.getCommandStack().execute(rotator);

    }



    // document.querySelector('svg').setAttribute("height", $('#imgSVG').attr("width"));
    //  rotate(figure, 90);


    /*  var command =test3.createCommand(new draw2d.command.CommandType(draw2d.command.CommandType.ROTATE));
     if (command !== null) {
     canvas.getCommandStack().execute(command);
     }*/
    //  test3.repaint();
    //var test= canvas.getFigures();

    /*  var command =figure.createCommand(new draw2d.command.CommandType(draw2d.command.CommandType.ROTATE));
     if (command !== null) {
     canvas.getCommandStack().execute(command);
     }*/

    SaveSheetFigures();
    SaveSheetLines();
    SaveSheetSVG();
}


function Calculate() {


    SaveSheetFigures();
    SaveSheetLines();
    SaveSheetSVG();

    /*    var figure=  canvas.getFigures();

     for (var i=0;i< figure['data'].length;i++)
     {



     canvas.add( figure['data'][i]);
     }



     document.getElementById('imgSVG').style.display='none';
     var  sheight = document.querySelector('svg').getAttribute('height');
     var  swidth= document.querySelector('svg').getAttribute('width');


     var    canvas1  = document.getElementById('CanTest');
     canvas1.height=sheight ;
     canvas1.width =swidth;

     var   ctx = canvas1.getContext("2d");
     ctx.clearRect(0,0 ,swidth,sheight);
     var svgString = new XMLSerializer().serializeToString(document.querySelector('svg'));




     var DOMURL = self.URL || self.webkitURL || self;
     var img = new Image();
     var svg = new Blob([svgString], {type: "image/svg+xml;charset=utf-8"});
     //   var svg = new Blob(svg, {type: "image/xml;charset=utf-8"});
     var url = DOMURL.createObjectURL(svg);
     img.onload = function() {
     ctx.drawImage(img, 0, 0);

     var imgData=ctx.getImageData(0,0,canvas1.width, canvas1.height);
     var dlina =0 ;
     var scale =0 ;
     var  area =0 ;

     for (var i=0; i<imgData.data.length; i+=4)
     {
     if (imgData.data[i]!=0){
     var test=    imgData.data[i];
     var test2=    imgData.data[i+1];
     var test3=    imgData.data[i+2];
     var test4=     imgData.data[i+3];

     if( (test==29) && (test2==29) && (test3==255))
     {

     dlina++;
     }

     if( (test==255) && (test2==8) && (test3==0))
     {

     scale++;
     }

     if( (test==29) && (test2==29) && (test3==255))
     {


     area++;
     }


     }
     }


     if (dlina!=0)
     {
     var  scalepx ;
     var  test1 ;
     var  sarea ;
     scalepx =   $('#txtFeet').val()/ 123 ;
     var  test2=    (scalepx*2);
     var  test3=    test2*test2;
     sarea =  area * test3;
     test1 =dlina* scalepx;
     alert ( "Scale:"+scalepx+"  Perimeter:"+  test1 +"   Area: "+ sarea + "  " + scale + " " + area);
     }
     document.getElementById('imgSVG').style.display='block';

     };
     img.src = url;

     */

}
function showFirstLineMsg(e) {
    $("#FirstLineMsg").css('left', (e.pageX + 10) + 'px').css('top', (e.pageY + 10) + 'px');
    $("#FirstLineMsg").show();
}
function FirstLineMsgHide(e) {
    $("#FirstLineMsg").hide();
}

function SaveScale() {
    if (($('#txtFeet').val().trim() != '') && ($('#txtInch').val().trim() != '')) {


        var hasScale = (Number.isInteger(Number($('#txtFeet').val().trim())) == true)
            && (Number.isInteger(Number($('#txtInch').val().trim())) == true);

        if ( hasScale ) {
            if (doFirstLineUX == true) {
                document.getElementById('aScale').style.color = "grey";
                svg.onmousedown = svg.touchstart = startDrawingLine;
                svg.onmouseup = svg.touchend = ferstDrawingLine;
                document.getElementById('aLine').style.color = "";
                svg.onmousemove = svg.touchmove = showFirstLineMsg;
                svg.onmouseleave = FirstLineMsgHide;
            } else {
                $("#FirstLineMsg").hide();
                document.querySelector('svg').style.cursor = "";
                document.getElementById('aScale').style.color = "";
                // document.getElementById('aLine').style.color="grey";
            }

            svg.contextmenu = Clear;
            svg.oncontextmenu = Clear;
            //  document.getElementById('aLine').style.color="";
            document.getElementById('aPolyLine').style.color = "";
            document.getElementById('aPolygon').style.color = "";
            document.getElementById('aMove').style.color = "";
            document.querySelector('svg').style.cursor = "crosshair";
            Line = false;
            PolyLine = true;
            Polygon = true;

            TakeOffLite.popUpHide();

            var test = $('#txtFeet').val();
            var test2 = $('#txtInch').val();
            var test3 = $('#txtInch').val();

            TakeOffLite.startProcess('SaveSheetsScale');
            $.ajax({
                url: 'Projects.asmx/SaveSheetsScale',
                data: {
                    Client: projectVars.userId,
                    SheetID: urlVars.sh,
                    ScaleFeet: $('#txtFeet').val(),
                    ScaleInch: $('#txtInch').val()
                },
                async: false,
                method: 'post',
                dataType: 'xml',
                success: function (data) {
                    TakeOffLite.endProcess('SaveSheetsScale');
                    $('#lbScale').text($('#txtFeet').val() + "'" + $('#txtInch').val() + "''");
                },
                error: errorHandlerMisc
            });
            $("#lbError").text("");
        } else {
            $("#lbError").text("Feet and Inch must be numbers");
        }
    } else {
        // invalid
        $("#lbError").text("Feet and Inch can not be empty");
        //  popUpShow();
    }
}


function CheckSheetObjectID(objid) {

    if ($('#' + objid).text().trim().length > 10) {

        NewText = $('#' + objid).text().trim().slice(0, 10) + "...";
        $('#' + objid).text(NewText);

    }
}

function CheckSheetName(objid) {

    if ($('#tab-content' + objid).text().trim().length > 13) {

        NewText = $('#tab-content' + objid).text().trim().slice(0, 13) + "...";
        $('#tab-content' + objid).text(NewText);

    }
}
var curDown = false
var curYPos = 0;
var curXPos = 0;

function FirstScale() {
    //  if (doFirstLineUX== true)
    {
        takeOffLite.popUpShow();
        doFirstLineUX = true;
    }
    /*  else
     {
     ClearBoot();

     }*/

}
function MoveMousemove(e) {

    if (curYPos > e.pageY) {
        if (curDown == true) {
            $('#gfx_holder').animate({
                scrollTop: $('#gfx_holder').scrollTop() + (50)

            }, 2, 'linear');

        }
    }
    if (curYPos < e.pageY) {
        if (curDown == true) {
            $('#gfx_holder').animate({
                scrollTop: $('#gfx_holder').scrollTop() - (50)
            }, 2, 'linear');


        }
    }

    if (curXPos > e.pageX) {
        if (curDown == true) {
            $('#gfx_holder').animate({
                scrollLeft: $('#gfx_holder').scrollLeft() + (50)

            }, 2, 'linear');

        }
    }
    if (curXPos < e.pageX) {
        if (curDown == true) {
            $('#gfx_holder').animate({
                scrollLeft: $('#gfx_holder').scrollLeft() - (50)
            }, 2, 'linear');

        }
    }
    /*   if(curDown==true){   $('#gfx_holder').animate({
     scrollTop: $('#gfx_holder').scrollTop() +( curYPos -(e.pageY)) ,
     scrollLeft: $('#gfx_holder').scrollLeft() +( curXPos -( e.pageX ))
     },20, 'linear');

     }*/
}
;

function MoveMousedown(e) {
    document.querySelector('svg').style.cursor = "grabbing";

    curYPos = e.pageY;
    curXPos = e.pageX;
    curDown = true;
    canvas.installEditPolicy(new draw2d.policy.canvas.BoundingboxSelectionPolicy()).off();
}
;

function MoveMouseup() {
    document.querySelector('svg').style.cursor = "grab";
    curDown = false;
    canvas.installEditPolicy(new draw2d.policy.canvas.BoundingboxSelectionPolicy()).on();
}
;

function Move() {
    document.getElementById('aMove').style.color = "grey";
    document.getElementById('aLine').style.color = "";
    document.getElementById('aPolyLine').style.color = "";
    document.getElementById('aPolygon').style.color = "";
    svg.contextmenu = Clear;
    svg.oncontextmenu = Clear;

    curDown = false
    curYPos = 0;
    curXPos = 0;

    document.querySelector('svg').style.cursor = "grab";
    document.getElementById("gfx_holder").onmousemove = MoveMousemove;
    document.getElementById("gfx_holder").onmousedown = MoveMousedown;
    document.getElementById("gfx_holder").onmouseup = MoveMouseup;
    // document.getElementById("gfx_holder").style.zIndex="1000";

}

var scrollLeft;
var scrollTop;
function MoveMousemoveX(e) {

    scrollLeft = $('#gfx_holder').scrollLeft() + (20);
    //  old= e.pagex;

    $('#gfx_holder').animate({

        scrollLeft: scrollLeft

    }, 1, 'linear');
}
;

function MoveMousemove2X(e) {



    scrollLeft = $('#gfx_holder').scrollLeft() - (20);
    //  old= e.pagex;

    $('#gfx_holder').animate({

        scrollLeft: scrollLeft

    }, 1, 'linear');

}
;

function MoveMousemoveY(e) {

    scrollTop = $('#gfx_holder').scrollTop() + (20);


    $('#gfx_holder').animate({

        scrollTop: scrollTop

    }, 1, 'linear');
}
;

function MoveMousemove2Y(e) {


    scrollTop = $('#gfx_holder').scrollTop() - (20);


    $('#gfx_holder').animate({

        scrollTop: scrollTop

    }, 1, 'linear');

}
;
function CheckSheetObjectName() {
    var Text = $('#txtNameObj').val();
    if ($('#txtNameObj').val().trim() != '') {

        /* check sheet object name */
        TakeOffLite.startProcess('CheckSheetObjectName');
        $.ajax({
            url: 'Projects.asmx/CheckSheetObjectName',
            data: {
                Client: projectVars.userId,
                SheetID: urlVars.sh,
                Name: Text
            },
            async: false,
            method: 'post',
            dataType: 'xml',
            success: function (data) {

                TakeOffLite.endProcess('CheckSheetObjectName');

                if ($(data).find('MessageError').text() != "") {
                    $('#ErrorMessage').text($(data).find('MessageError').text());

                } else {
                    $('#ErrorMessage').text("");
                    //  $('#txtNameObj').val("");
                    TakeOffLite.popUpHide2();

                }
            },
            error:
                function (data) {

                }
        });
    }
}
function SaveScaleID(obj) {
    var ID = obj.id.replace('liScale', '');

    TakeOffLite.startProcess('SaveScaleID');
    $.ajax({
        url: 'Projects.asmx/SaveScaleID',
        data: {
            Client: projectVars.userId,

            SheetID: urlVars.sh,
            ScaleID: ID

        },
        async: false,
        method: 'post',
        dataType: 'xml',
        success: function (data) {
            TakeOffLite.endProcess('SaveScaleID');
            $('.liAclive').removeClass("liAclive");
            $('#' + obj.id).addClass("liAclive");
        },
        error: errorHandlerMisc
    });

}

function HideLi(obj) {

    if ($('#' + obj).find('ul').children().length == 0) {
        $('#' + obj).hide();
    } else {
        $('#' + obj).show();
    }

}

function Hide(obj, obj2) {

    if ($(obj2).attr('class') == "TakeoffsOpen") {
        $(obj2).attr('class', 'TakeoffsClose');
        $('#' + obj).hide();
    } else {
        $(obj2).attr('class', 'TakeoffsOpen');
        $('#' + obj).show();
    }

}


function Hide1(obj, obj2) {

    if ($(obj2).attr('class') == "TakeoffsOpen") {
        $(obj2).attr('class', 'TakeoffsClose');
        $('#' + obj).hide();
    } else {
        $(obj2).attr('class', 'TakeoffsOpen');
        $('#' + obj).show();
    }

}

var showScaleMenu = true;
function onClickToolScale() {
    if (showScaleMenu == true) {
        let menuElem = document.getElementById('sweeties');
        let test = document.getElementById('taskbar');
        $('#sweeties').css({"color": " grey"});
        $('#ulScale').css({"width": "150px", "position": " fixed", "left": menuElem.offsetLeft + "px", "top": menuElem.offsetTop + test.offsetHeight - 10 + "px", "display": " block", "zIndex": "99999999"});
        // menuElem.classList.toggle('open');
        showScaleMenu = false;
    } else {
        showScaleMenu = true;
        $('#sweeties').css({"color": "  white"});
        $('#ulScale').css({"display": " none"});
    }
}

/**
 * check for image?  This may not be necessary anymore
 * @returns {undefined}
 */
function drawCanvasImg(){



    var hasImgUrlAndId = !isUndefined(drawingData)
        && drawingData.imgUrl !== ''
        && drawingData.imgUrl !== undefined
        && urlVars.id !== '';

    console.log('DEPRECATED: [drawCanvasImg] hasImgUrlAndId: ', [hasImgUrlAndId, drawingData]);

    return false;

    // this is what it used to do:

    if (hasImgUrlAndId) {
        // GET THE IMAGE.
        var img = new Image();
        img.src = drawingData.imgUrl;
        img.onload = function () {
            fill_canvas(img);       // FILL THE CANVAS WITH THE IMAGE.
        };
        // WAIT TILL IMAGE IS LOADED.


        function fill_canvas(img) {

            console.log('TODO: fill_canvas?');

            //      var canvas = document.getElementById('<%=ViewSheet.ClientID%>');
            //      var ctx = canvas.getContext('2d');
            //     canvas.width = img.width;
            //     canvas.height = img.height;
            //     ctx.drawImage(img, 0, 0);
        }
    }
}