var TakeOffLiteApp = function () {

    var __VERSION = '1.0.0';
    var $;
    var urlVars = getUrlVars();
    window.urlVars = urlVars;

    /*
     * initialize js app
     */
    function init() {

        console.log('===[ Takeofflite JS App ]=== v: ' + __VERSION, [this, urlVars]);



        // wait for jquery
        if (typeof jQuery === 'undefined') {
            console.log('[tkl js] ...Waiting for jQuery...');
            setTimeout(init, 1000);
        }

        // jquery loaded... initialize uis
        $ = jQuery; // add shorthand

        // prototypes
        addProtos();

        // redirects
        doRedirects();

        // detect page
        detectPageMode();

        // ui
        initUI();

        // init PDF upload
        initPDFCanvas();

        // detect mode from url params etc
        initPageMode();// also adds body class
    }

    /**
     * do redirects - these can all be moved to server but will be patches here in the mean time
     * @returns {undefined}
     */
    function doRedirects(){
        if( String(getUrlVars().id) === '0' ){
            window.location = String(window.location).split('?')[0];
        }
    }

    function popUpShow() {
        $("#popup1").show();
    }
    function popUpHide() {
        $("#popup1").hide();
    }
    function popUpShow2() {
        $("#popup2").show();
    }
    function popUpHide2() {
        $("#popup2").hide();
    }

    function goToSheetListPage(__addToURL) {
        console.log('[takeofflite] going to sheet list page...');

        startProcess('Going to Sheet List Page...');

        var sheetListUrl = '/Projects?id=' + urlVars.id + '&op2=1';
        if(!isUndefined(__addToURL) && __addToURL.length > 0){
            sheetListUrl += __addToURL;
        }
        window.location = sheetListUrl;
    }

    function goToEstimatingPage(__addToURL) {
        console.log('[takeofflite] going to estimating page...');
        var estimatingUrl = '/Projects?id=' + urlVars.id + '&sh=0';
        if(!isUndefined(__addToURL) && __addToURL.length > 0){
            estimatingUrl += __addToURL;
        }
        window.location = estimatingUrl;
    }

    function showTab(tab)
    {

        console.log('Showing Tab: ' + tab);

        switch (tab) {

            // measuring / "measure"
            case "measurements":
                $(".measure-tab").css("background-color", "white");
                $(".estimate-tab").css("background-color", "#ddd");
                $(".assemblies").hide();
                $(".items").hide();
                $(".measurements").show();

                // switch to sheet list if measure mode is selected while in a spreadsheet
                if (isSpreadSheet) goToSheetListPage();

                break;

            // estimating / "estimate"
            case "items":
                $(".measure-tab").css("background-color", "#ddd");
                $(".estimate-tab").css("background-color", "white");
                $(".assemblies").show();
                $(".items").show();
                $(".measurements").hide();

                if (isOtherSheet || isSheetList) {
                    console.log('Switching from Measure Mode to Estimating Mode...', [isOtherSheet, isSheetList]);
                    goToEstimatingPage();
                }

                break;

            default:
                break;

        }
    }

    /**
     *	Add a body class relative to the current page for CSS to pivot page for page
     *	This may be able to be replaced if the ASP app drops relevant page classes to the body class instead
     */
    var urlAfterDomain	= String(window.location).split('takeofflite.com')[1];
    var isProjectsDash	= urlAfterDomain === '/Projects';
    var hasSheetId		= !isUndefined(urlVars.sh);
    var isSpreadSheet	= hasSheetId && urlVars.sh === '0';
    var isOtherSheet	= hasSheetId && !isSpreadSheet;
    var isDrawingPage	= isOtherSheet;
    var isCostItems		= !isUndefined( urlVars.op ) && urlVars.op === 'items';
    var isAssemblies	= !isUndefined( urlVars.op ) && urlVars.op === 'assembly';
    var isSheetList		= !isUndefined(urlVars.op2) && urlVars.op2 === '1';

    /**
     * detect page mode and init for that mode.
     * @returns {undefined}
     */
    function detectPageMode(){
        urlAfterDomain	= String(window.location).split('takeofflite.com')[1];
        isProjectsDash	= urlAfterDomain === '/Projects';
        hasSheetId		= !isUndefined(urlVars.sh);
        isSpreadSheet	= hasSheetId && urlVars.sh === '0';
        isOtherSheet	= hasSheetId && !isSpreadSheet;
        isSheetList		= !isUndefined(urlVars.op2) && urlVars.op2 === '1';
        isAssemblies	= !isUndefined( urlVars.op ) && urlVars.op === 'assembly';
        isCostItems = !isUndefined( urlVars.op ) && urlVars.op === 'items';
    }

    // init for the current mode.
    function initPageMode() {

        console.log('[initPageMode]');

        if (isProjectsDash)	initDash();
        if (isSpreadSheet)	initSpreadSheet();
        if (isSheetList)	initSheetList();
        if (isOtherSheet)	initDrawing();
        if (isCostItems)	initCostItems();
        if (isAssemblies)	initAssemblies();
    }

    function layoutNoSideBar(){
        $('body').addClass('tkl-no-sidebar');
    }

    function layoutNoProjectSelector(){
        $('body').addClass('tkl-no-project-selector');
    }

    function layoutNoTabRow(){
        $('body').addClass('tkl-no-tab-row');
    }

    /**
     * cost items page
     * @returns {undefined}
     */
    function initAssemblies(){
        layoutNoProjectSelector();
        layoutNoSideBar();
        layoutNoTabRow();
        $('body').addClass('tkl-assemblies');
    }

    /**
     * cost items page
     * @returns {undefined}
     */
    function initCostItems(){
        layoutNoSideBar();
        layoutNoTabRow();
        layoutNoProjectSelector();
        $('body').addClass('tkl-cost-items');
    }

    function isMeasuringForCell(){
        var measuring = localStorage.getItem('tklGettingMeasurement');
        if(measuring === 'true' || measuring === true){
            return true;
        } else {
            return false;
        }
    }

    /**
     * dismiss target cell notice
     * @returns {undefined}
     */
    function dismissTargetCell(){
        console.log('Hiding this...', this);
        TakeOffLite.hideTakeoffBar();
        disableTargetCellMode();
    }

    /**
     * disable target cell mode
     * @returns {undefined}
     */
    function disableTargetCellMode(){

        localStorage.setItem('tklGettingMeasurement',false);

    }

    /**
     * initialize drawing mode
     * @returns {undefined}
     */
    function initDrawing() {

        $('body').addClass('tkl-sheet-drawing');
        clickMeasureTab();

        // has local data with target cell?
        if(isMeasuringForCell()){
            addDrawingTakeoffBar();
        }

        setTimeout(initTOLDrawing,500);// Temp fix: there was some race condition with projectVars being set

        // enable the copy buttons in the left nav
        setTimeout(enableMeasurementItems,800);
    }

    /**
     * measurement items
     * @returns {undefined}
     */
    function enableMeasurementItems(){

        //console.log('Enabling measurement items total: ' + $('li.measurement-item .values-wrapper label').length);

        // enable area and perim click to copy
        $('li.measurement-item .values-wrapper label').click(onClickMeasurementLabel);
    }

    /**
     * Add drawing takeoff bar
     * @returns {undefined}
     */
    function addDrawingTakeoffBar(){
        var targetRow = localStorage.getItem('tklTargetRow');
        var targetCol = localStorage.getItem('tklTargetCol');
        var targetName = localStorage.getItem('tklTargetName');
        var targetCell = targetRow + ' - ' + targetCol;
        var barButtons = getTakeoffBarButtons();
        //var barContent = 'Create or Select a Measurement for Cell ' + targetCell;
        var barContent = '<span class="tbar-message">Create or Select a Measurement for <b>' + targetName + '</b></span>';
        showTakeoffBar( barContent + barButtons );
        $('#takeoff-bar .action-buttons').hide();
    }

    // takeoff bar buttons
    function getTakeoffBarButtons(){
        var btns = '<span class="action-buttons">';

        if(isDrawingPage){
            /*
            + ' <a class="copy-perimeter btn">Copy Perimeter</button>'
            + ' <a class="copy-area btn">Copy Area</a>'
            */
            btns += ' | <a class="use-perimeter btn">Send Perimeter</a>'
                +  ' <a class="use-area btn">Send Area</a>';
        }

        btns +=  getCellTargetCancelButton();
        btns += ' </span>';

        return btns;
    }

    function getCellTargetCancelButton(){
        return ' | <a onclick="TakeOffLite.dismissTargetCell();" class="dismiss-btn">cancel</a>';
    }

    /**
     * clicking on area or perimeter
     * @returns {undefined}
     */
    function onClickMeasurementLabel(__e){
        console.log('Clicked: ', this);
        var this$ = $(this);
        var isArea = this$.hasClass('measurement-area');
        var isPerim= this$.hasClass('measurement-perimeter');
        var labelName = isPerim ? 'Perimeter' : 'Area';
        if(isArea || isPerim){
            var valToCopy = this$.text().split(':')[1];
            copyTextToClipboard(valToCopy);
            lastTakeoffVal = valToCopy;
            var hasTakeoffBar = $('#takeoff-bar').length > 0;
            if(hasTakeoffBar){
                var tBar$ = $('#takeoff-bar');
                var newBarHTML = labelName + ' <code>' + valToCopy + '</code> Copied!'
                    + ' &nbsp;&nbsp;&nbsp; Send to: <b>'
                    + localStorage.getItem('tklTargetName') + '? </b> ';
                tBar$.find('.tbar-message').html(newBarHTML);

                var hasButtons = tBar$.find('.action-buttons').length > 0;
                if(!hasButtons){
                    var barButtons = getTakeoffBarButtons();
                    tBar$.append(barButtons);
                    enableTakeoffBarButtons();
                }

                $('.action-buttons').show();

                // hide/show action buttons
                if(isArea){
                    $('a.use-perimeter.btn').hide();
                    $('a.use-area.btn').show();
                } else if(isPerim) {
                    $('a.use-perimeter.btn').show();
                    $('a.use-area.btn').hide();
                } else {
                    $('a.use-perimeter.btn').show();
                    $('a.use-area.btn').show();
                }
            }
        }


    }

    /**
     * My Projects dashboard page
     * @returns {undefined}
     */
    function initDash() {
        $('body').addClass('projects-dashboard');
    }

    /**
     * init spread sheet
     * @returns {undefined}
     */
    function initSpreadSheet() {

        trace('[initSpreadSheet] initializing for page mode Spread Sheet');

        $('body').addClass('tkl-spreadsheet'); // mark mode

        clickEstimateTab(); // click estimates tab to show estimates panels in left col

        // init / create and load the spreadsheet
        createSpreadSheet();


        var hasAreaToInsert		 = !isUndefined(urlVars.area) && Number(urlVars.area) >= 0;
        var hasPerimToInsert	 = !isUndefined(urlVars.perim) && Number(urlVars.perim) >= 0;
        var hasTargetDataWaiting = hasAreaToInsert || hasPerimToInsert;
        var localTOLData		 = getLocalDataTarget();

        console.log('[initSpreadSheet] Initializing spreadsheet! has data waiting to insert? ' + hasTargetDataWaiting, localTOLData);

        // this is to insert the 1 cell from taking a measurement
        if(hasTargetDataWaiting && localTOLData.active) {
            setTimeout(insertDataToSS,2000);
        }
    }

    /**
     * spreadsheet data insert
     * @returns {undefined}
     */
    var targetCell;
    function insertDataToSS() {

        startProcess('Inserting Takeoff Value');

        // check for target cell
        var targetTOLData = getLocalDataTarget();

        // check for data waiting
        var hasAreaToInsert = !isUndefined(urlVars.area) && Number(urlVars.area) >= 0;
        var hasPerimToInsert = !isUndefined(urlVars.perim) && Number(urlVars.perim) >= 0;
        var hasTargetDataWaiting = hasAreaToInsert || hasPerimToInsert;

        // insert the data into the cell
        var targetVal = '...';
        if(hasPerimToInsert) targetVal = urlVars.perim;
        if(hasAreaToInsert) targetVal = urlVars.area;
        var measureActive = targetTOLData.active === 'true' || targetTOLData.active === true;
        if(hasTargetDataWaiting && measureActive){

            targetCell = getActiveSheet().getCell(Number(targetTOLData.row),Number(targetTOLData.col));

            console.log('Inserting takeoff value into cell/val: ', [ targetCell, targetVal ]);

            targetCell.backColor('Green'); // insert it
            targetCell.foreColor('White'); // insert it
            targetCell.value(targetVal); // insert it

            // show a message
            //showNotice('Inserted Takeoff Quantity (' + targetVal + ') for Item: ' + targetTOLData.name);
            console.log('Inserted Takeoff Quantity (' + targetVal + ') for Item: ' + targetTOLData.name);

            // save the change?
            setTimeout(SaveSSItem,1000);

            // wait 5 seconds and set it back
            setTimeout(resetCellStyle, 3000, targetCell);

            // only do it once
            disableTargetCellMode();
        }

        // end the spinner
        setTimeout(TakeOffLite.endProcess,2000,'Inserting Takeoff Value');
    }

    /**
     * show notice!
     * @param {type} __msg
     * @returns {undefined}
     */
    function showNotice(__msg){
        var container$ = $('#Sheet').first();
        var noticeMarkup = '<div class="notice"><p>'
            + __msg
            + '</p><a href="#" class="dismiss-link">Dismiss</a></div>';
        container$.prepend(noticeMarkup);
        $('.dismiss-link').click(dismissMe);
    }

    /**
     * click handler to hide the parent
     * @param {type} __e
     * @returns {undefined}
     */
    function dismissMe(__e){
        console.log('dismissing (hiding) parent of: ', [this,__e]);
        $(this).parent().hide();
    }
    window.dismissMe = dismissMe;

    /**
     * reset the style of a cell
     * @returns {undefined}
     */
    function resetCellStyle(__cell){

        console.log('Resetting cell style for: ', __cell);
        __cell.backColor('#FFFFFF');
        __cell.foreColor('Black');
        __cell.borderLeft(	new GC.Spread.Sheets.LineBorder("Grey", GC.Spread.Sheets.LineStyle.hair ) );
        __cell.borderRight(	new GC.Spread.Sheets.LineBorder("Grey", GC.Spread.Sheets.LineStyle.hair ) );
        __cell.borderTop(	new GC.Spread.Sheets.LineBorder("Grey", GC.Spread.Sheets.LineStyle.hair ) );
        __cell.borderBottom(new GC.Spread.Sheets.LineBorder("Grey", GC.Spread.Sheets.LineStyle.hair ) );
    }

    /**
     * default cell style for spread js sheet
     * @returns {TakeOffLiteApp.getDefaultCellStyle.style1|GcSpread.Sheets.Style}
     */
    function getDefaultCellStyle(){
        var ns = GcSpread.Sheets;
        var style1 = new GcSpread.Sheets.Style();
        style1.backColor	= "#FFFFFF";
        style1.borderLeft	= new ns.LineBorder("grey", ns.LineStyle.hair);
        style1.borderTop	= new ns.LineBorder("grey", ns.LineStyle.hair);
        style1.borderRight	= new ns.LineBorder("grey", ns.LineStyle.hair);
        style1.borderBottom = new ns.LineBorder("grey", ns.LineStyle.hair);
        return style1;
    }


    function initSheetList() {

        // mark dom
        $('body').addClass('tkl-sheets-list');

        // left side bar menu
        clickMeasureTab();

        // enable the left menu copyu
        setTimeout(enableMeasurementItems,1000);

        // takeoff bar notice
        var measuring = localStorage.getItem('tklGettingMeasurement');
        if(measuring === 'true' || measuring === true){
            showTakeoffBar();
        }

        var hasSheets = $('#SheetList li.SheetList').length > 0;
        if(hasSheets){

        } else {
            var icon = '<i class="fas fa-folder-plus" aria-hidden="true"></i>';
            var noSheetsMsg = '<h3 class="notice soft">To add your first Sheet, press the ' + icon + ' icon in the main toolbar.</h3>';
            $('div.spreadsheet-container').append(noSheetsMsg);
        }
    }

    function clickMeasureTab() {
        console.log('clicking measure tab.');
        $('a.sidebar-tab.measure-tab').trigger('click');
    }


    function clickEstimateTab() {
        console.log('clicking estimate tab.');
        $('a.sidebar-tab.estimate-tab').trigger('click');
    }


    function hideTakeoffBar(){
        var tob$ = $('#takeoff-bar');
        tob$.hide();
    }

    /**
     *
     * @param {type} __args
     * @returns {undefined}
     */
    function updateTakeoffBar(__args){

        var row = __args.row;
        var col = __args.col;

        if( TakeOffLite.isSpreadSheet() ){
            var buttons = getActionButtonsForCell(row,col);
            var content = '<p>Get a Takeoff for This Cell? '
                + '<span class="cell-target">'+(row+1)+' - '+ col + '</span>'
                + '</p>'
                + '<span class="cell-actions">'+buttons+'</span>';
        }

        //TODO: this might be unnecessary now but could move code here instead
        /*
        if( TakeOffLite.isSheetList() ){
            var content = 'Choose a Sheet to Get the Measurement.';
        }
        */

        $('#takeoff-bar').html(content);
    }


    /**
     *
     * @returns {undefined}
     */
    function selectSideBarTab() {

    }

    /** add useful prototypes to jquery and other classes **/
    function addProtos() {

        // url params
        $.urlParam = function (name, url) {
            if (!url) {
                url = window.location.href;
            }
            var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(url);
            if (!results) {
                return undefined;
            }
            return results[1] || undefined;
        };
    }


    /**
     * initialize the ui
     * @returns {undefined}
     */
    function initUI() {

        console.log('[takeofflite] Initializing UI...');

        // white labels
        checkForWhiteLabels();

        // hide elements
        initialHides();

        // menus
        enableMenus();

        // init load/unload
        initPageEvents();

        // legacy code
        legacyUIInit();

        // init the drop zone are for file upload ui
        initDropZone();
    }

    /**
     * drop zone
     * @returns {undefined}
     */
    function initDropZone(){
        if (window.File && window.FileList && window.FileReader) {
            /************************************
             * All the File APIs are supported. *
             * Entire code goes here.           *
             ************************************/
            /* Setup the Drag-n-Drop listeners. */
            var dropZone$ = $('#drop_zone');
            if(dropZone$.length > 0){
                dropZone$.on('dragover',handleDragOver);
                dropZone$.on('dragleave',handleDragOut);
                dropZone$.on('drop',handleDnDFileSelect);
                //			dropZone.addEventListener('dragover', handleDragOver, false);
                //			dropZone.addEventListener('drop', handleDnDFileSelect, false);
            }

            dropZone$.on('click', function () {
                $("#file-to-upload").trigger('click');
                return false;
            });


            // When user chooses a PDF file
            $("#file-to-upload").on('change', function () {

                // Validate whether PDF
                var files = $("#file-to-upload").get(0).files[0];

                console.log('File To Upload Changed! Validating PDF File(s): ', files);

                if (['application/pdf'].indexOf(files.type) == -1) {
                    alert('Error : File is Not a PDF.');
                    return false;
                }

                if (files != 0) {
                    var form_data = new FormData();

                    form_data.append('Client', projectVars.clientId);
                    form_data.append('job_file', files);

                    TakeOffLite.startProcess('AttributesPictureDataSet');
                    $.ajax({
                        type: "POST",
                        url: "projects.asmx/AttributesPictureDataSet",
                        data: form_data,
                        dataType: "xml",
                        contentType: false,
                        processData: false,
                        success: function (data) {

                            TakeOffLite.endProcess('AttributesPictureDataSet');

                            $("#id03").hide();
                            $("#pdf-main-container").show();

                            var fileObjURL = URL.createObjectURL(files);

                            trace('PDF Uploaded.  Processing...');

                            //debugger
                            showPDF(fileObjURL);
                        },

                        error: errorHandlerMisc
                    });
                }
            });



        } else {
            alert('Sorry! this browser does not support HTML5 File APIs.');
        }
    }

    var files;
    function handleDragOver(event) {

        console.log('drag over...');

        event.stopPropagation();
        event.preventDefault();
        var dropZone = document.getElementById('drop_zone');
        //dropZone.innerHTML = "Drop now";
        $('#drop_zone').addClass('dragging');
    }

    function handleDragOut(event){
        $('#drop_zone').removeClass('dragging');
    }

    /**
     * drag and drop PDF to upload
     * @param {type} event
     * @returns {Boolean}
     */
    function handleDnDFileSelect(__event) {

        __event.stopPropagation();
        __event.preventDefault();

        $('#drop_zone').removeClass('dragging');


        var event = __event.originalEvent;// original event seems to have the data transfer

        console.log('Handling file drop... ', event);

        var hasFiles = !isUndefined(event)
            && !isUndefined(event.dataTransfer)
            && !isUndefined(event.dataTransfer.files);

        if(hasFiles){
            /* Read the list of all the selected files. */
            files = event.dataTransfer.files;
            var notPDF = ['application/pdf'].indexOf(files[0].type) == -1;
            if (notPDF) {
                alert('Error : Not a PDF');
                return false;
            }
        } else {
            alert('Error: no PDF files to upload.');
            return false;
        }

        // has files...
        var firstFile = files[0];
        var hasFirstFile = !isUndefined(firstFile) && firstFile != 0;
        if (hasFirstFile) {

            var form_data = new FormData();
            form_data.append('Client', projectVars.userId);
            form_data.append('job_file', files[0]);

            TakeOffLite.startProcess('AttributesPictureDataSet');
            $.ajax({
                type: "POST",
                url: "projects.asmx/AttributesPictureDataSet",
                data: form_data,
                dataType: "xml",
                contentType: false,
                processData: false,
                success: function (data) {
                    TakeOffLite.endProcess('AttributesPictureDataSet');
                    $("#id03").hide();
                    $("#pdf-main-container").show();
                    showPDF(URL.createObjectURL(files[0]));
                },
                error: errorHandlerMisc
            });
        }
    }

    /**
     * catch some page level events like unload
     * @returns {undefined}
     */
    function initPageEvents() {

        $(window).on('beforeunload', onPageUnload);
        window.addEventListener('pageshow', function (event) {
            if (!event.persisted) {
                return;
            }
            var fader = document.getElementById('tkl-unloading');
            fader.classList.remove('tkl-unloading');
        });
    }

    //todo: refactor and eliminate this function
    function legacyUIInit() {

        //?
        popUpHide();
        popUpHide2();

        console.log('Legacy Init... checking urlVars: ', urlVars);

        // TODO: what is this condition
        var someCondition = (urlVars.op2 === '1');
        if (someCondition) {
            $('#SheetList').css("display", "block");
            //  $('#divView').css("display", "block");
            $('#divCont').css("display", "block");

            var SelectSH = document.getElementById('tab-content-1');
            SelectSH.parentElement.classList.add('tabbar-tab-button--active');
        }

        if ((urlVars.id == '') || (urlVars.sh == '0')) {
            $('#divView').css("display", "none");
        }
        if ((urlVars.op == 'edit') || (urlVars.op == 'items') || (urlVars.op == 'assembly')) {
            $('#divView').css("display", "block");
            $('#divCont').css("display", "none");
        }

        var hasSheetId = !isUndefined(urlVars.sh);

        console.log('Has Sheet Id? ' + hasSheetId, urlVars.sh);

        if (hasSheetId && urlVars.sh !== '') {
            var SelectSH = document.getElementById('tab-content' + urlVars.sh);
            SelectSH.parentElement.classList.add('tabbar-tab-button--active');

            if (SelectSH.offsetLeft > document.getElementById('TabSH').offsetWidth - 100) {
                document.getElementById('divTab').scrollLeft = SelectSH.offsetLeft;
            }
        }

        var isSheet0 = String(urlVars.sh) === '0';
        var isOp2SetTo1 = String(urlVars.op2) === '1';
        if (isSheet0 && !isOp2SetTo1) {
            $('#cont').css("display", "block");
        }
        if ((urlVars.id == '')) {
            $('#li0').css("display", "none");
            $('#li-1').css("display", "none");
        }
    }

    /**
     * page unloading / changing
     * @returns {undefined}
     */
    function onPageUnload(e) {
        //alert('unloading');
        $('body').addClass('tkl-unloading');
        $('body').css('opacity', '.5');
    }

    /**
     * check for white label trigger (demo / will be replaced)
     */
    function checkForWhiteLabels() {

        var cUrl = String(window.location);
        var wlName = $.urlParam('wl');


        // name override is in URL, prefer it to overried local storage cache
        if(!isUndefined(wlName) && wlName.length > 0){
            setLocalStorage('tklWhiteLabel',wlName);
        }


        var hasSub = cUrl.indexOf('.takeofflite.com') > 0 && cUrl.indexOf('www.takeofflite.com') < 0;
        var localStorageName = localStorage.getItem('tklWhiteLabel');



        var hasLocalWL = !isUndefined(localStorageName) && localStorageName.length > 0;
        if (hasLocalWL && isUndefined(wlName))	wlName = localStorageName;

        console.log('Checking for white label: ' + hasLocalWL, [hasSub, wlName, hasWlName]);

        var hasWlName = isUndefined(wlName) || !(wlName.length > 0);
        if (hasWlName) {
            if (hasSub) {
                wlName = String(window.location).split('.takeofflite.com')[0];
                // strip proto off
                if (wlName.indexOf('http://') >= 0)
                    wlName = String(wlName).replace('http://', '');
                if (wlName.indexOf('https://') >= 0)
                    wlName = String(wlName).replace('https://', '');
            }
        }

        //
        if (wlName && wlName.length > 0) {
            console.log('white label param found: ' + wlName);
            loadWhiteLabelStyles(wlName);
        }
    }

    function setLocalStorage(__key, __val) {
        if (localStorage) {
            localStorage.setItem(__key, __val);
        } else {
            console.log('Error: cannot find Local Storage.  Please use the website in a non-incognito mode with cookies enabled.');
        }
    }

    /**
     * load a white label style sheet
     */
    function loadWhiteLabelStyles(__wlName) {

        var styleFile = 'whitelabels/' + __wlName;

        switch (__wlName) {

            case 'beehive':
            case 'test':
                loadStyleSheet(styleFile);
                setLocalStorage('tklWhiteLabel', __wlName);
                break;

            default:
                console.log('Warning: White Label name not recognized: ' + __wlName);
        }
    }

    /**
     * load a white label stylesheet
     */
    function loadStyleSheet(__fileName) {

        if (__fileName.indexOf('.css') < 0) {
            __fileName = __fileName + '.css';
        }

        var cssIncl = '<link rel="stylesheet" href="/css/' + __fileName + '">';

        console.log('Loading stylesheet: ' + cssIncl);

        $('body').prepend(cssIncl);
    }

    /** check an object for a parameter to avoid null ref **/
    function hasParam(__obj, __param) {
        try {
            var hasObj = typeof __obj !== 'undefined';
            var hasParam = hasObj && typeof __obj[String(__param)] !== 'undefined';
            return Boolean(hasObj && hasParam);
        } catch (e) {
            console.log('Warning: Error looking for param: ' + __param + ' on obj: ', __obj);
            return false;
        }
    }

    /**
     * enable main menus
     * @returns {undefined}
     */
    function enableMenus() {

        // main tabs
        $(".estimate-tab").click(function () {
            showTab("items");
        });
        $(".measure-tab").click(function () {
            showTab("measurements");
        });

        // add minimize button
        addMenuMinimizeButton();

        $('.openCloseNextUL').click(onClickToggleOpenCloseUL);

    }

    /**
     * Add Menu Minimize button
     * @returns {undefined}
     */
    function addMenuMinimizeButton() {
        $('aside').prepend('<a class="minimize"> <span class="icon">&lsaquo;</span> </a>');
        var collapseBtn$ = $('aside a.minimize');
        collapseBtn$.click(function () {
            $('body').toggleClass('side-nav-minimized');
            setTimeout(function () {
                $('window').trigger('resize');
                var event;
                if (typeof (Event) === 'function') {
                    event = new Event('resize');
                } else { /*IE*/
                    event = document.createEvent('Event');
                    event.initEvent('resize', true, true);
                }
                window.dispatchEvent(event);

            }, 1000);
        });
    }

    /**
     * open and close the menu section for the item clicked
     * @param {type} __e
     * @returns {undefined}
     */
    function onClickToggleOpenCloseUL(__e) {

        console.log('Toggling menu section...');

        var myNextUL$ = $(this).parent().find('ul');

        if (myNextUL$.is(':visible')) {
            myNextUL$.hide('slow');
            $(this).removeClass('TakeoffsOpen');
            $(this).addClass('TakeoffsClose');
        } else {
            myNextUL$.show('slow');
            $(this).removeClass('TakeoffsClose');
            $(this).addClass('TakeoffsOpen');
        }
    }

    function openCloseNextUL() {

    }

    /**
     * hiding some things on init
     * @returns {undefined}
     */
    function initialHides() {
        // submenu shows that shouldnt..
        $('#sweeties #ulScale').hide();

        $('#divView').hide();
    }


    /**
     * start process
     * @returns {undefined}
     */
    var processList = [];
    function startProcess(__processName) {

        console.log('[startProcess] Starting Process: ' + __processName);

        processList.push(__processName);

        if(processList.length > 0){
            $('body').addClass('tkl-working');
        }
    }

    /**
     * get the procss list and show it in the console
     * @returns {undefined}
     */
    function getProcessList(){
        console.log('Process List: ', processList);
        return processList;
    }

    /**
     * search and return total instances of process in list. no param for list total length
     * @param {type} __processName
     * @returns {undefined.length|TakeOffLiteApp.processList.length|Number}
     */
    function countProcesses(__processName){

        var ps = getProcessList();
        if(!__processName || isUndefined(__processName)){
            return ps.length;
        } else {
            var matches = 0;
            for(var i=0; i<ps.length;++i){
                if(ps[i].toLowerCase() === __processName.toLowerCase()){
                    matches++;
                }
            }
            return matches;
        }
    }

    /**
     * end process
     * @returns {undefined}
     */
    var waitAndHideSpinner;
    function endProcess(__processName) {

        console.log('[endProcess] Ending Process: ' + __processName);

        var t = processList.length;
        for (var i = 0; i < t; ++i) {
            var aProcess = processList[i];
            var match = String(aProcess).toLowerCase() === String(__processName).toLowerCase();
            if (match) {
                processList.splice(i, 1);
                break;
            }
        }

        var allComplete = !(processList.length > 0);
        if (allComplete) { // always wait a second before hiding spinner so that instantaneous processes can show they ran
            if(waitAndHideSpinner > 0) clearTimeout(waitAndHideSpinner);
            waitAndHideSpinner = setTimeout(onAllProcessComplete,1000);
        }
    }

    function onAllProcessComplete(){
        $('body').removeClass('tkl-working');
    }

    function getLocalDataTarget(){
        var targetRow = localStorage.getItem('tklTargetRow');
        var targetCol = localStorage.getItem('tklTargetCol');
        var isMeasure = localStorage.getItem('tklGettingMeasurement');
        var targetName = localStorage.getItem('tklTargetName');
        return {
            row: targetRow,
            col: targetCol,
            active: isMeasure,
            name: targetName
        };
    }

    /**
     *
     * @returns {undefined}
     */
    function showTakeoffBar(__newMsg){
        var tob$ = $('#takeoff-bar');
        var hasTob$ = tob$.length > 0;
        if(!hasTob$) { // create takeoff bar

            var barContent = '<div id="takeoff-bar">'+__newMsg + getTakeoffBarButtons() + '</div>';
            var barParent$ = $('#Sheet');
            var isSheetList = String(urlVars.op2) === '1';
            var isSpreadSheet = String(urlVars.sh) === '0';
            var localDataTarget = getLocalDataTarget();
            var targetNameFromStorage = localDataTarget.name;
            var itemName = isUndefined(targetNameFromStorage) ? '' : targetNameFromStorage;

            if( isSpreadSheet ) {
                barParent$ = $('#Sheet .spreadsheet-container button#but').parent();
            }

            if(isSheetList){
                barParent$ = $('div#SheetList');
                //var targetRow = localDataTarget.row;
                //var targetCol = localDataTarget.col;
                //var newCont = 'Choose a Document to Get Your Measurement from for cell: <b>' + targetRow + '-' + targetCol + '</b>';
                var newCont = '<span class="tbar-message tbmsg1">Choose a Document or Measurement to Get Your Measurement for: '
                    +'<b>' + itemName + '</b></span>'
                    + getCellTargetCancelButton();
                var barContent = '<div id="takeoff-bar">'+newCont+'</div>';
            }

            if(isOtherSheet){ // drawing
                //var targetRow = localDataTarget.row;
                //var targetCol = localDataTarget.col;
                barParent$ = $('div#divCont');
                var barSib$ = $('#divTab');
                $(barContent).insertAfter(barSib$);
            } else { // not drawing
                barParent$.prepend(barContent);
            }

            enableTakeoffBarButtons();
        }
        tob$ = $('#takeoff-bar');
        console.log('[showTakeoffBar] Showing takeoff bar: ', tob$);
        tob$.show();
    }

    function getTakeoffBar$(){
        return $('#takeoff-bar');
    }

    var lastTakeoffVal;
    function getCurrentTakeoffVal(){
        return lastTakeoffVal;
    }

    function onClickSendAreaOrPerim(__e){

        console.log('[onClickSendAreaOrPerim] sending area or perim to estimating sheet');

        var thisBtn$ = $(this);
        var isPerim = thisBtn$.hasClass('use-perimeter');
        var isArea = thisBtn$.hasClass('use-area');
        var valToSend = getCurrentTakeoffVal();
        valToSend = String(valToSend)
            .trim()
            .replace(' ',''); // sanitize a little
        if(isArea){
            goToEstimatingPage('&area=' + valToSend );
        } else if(isPerim){
            goToEstimatingPage('&perim=' + valToSend );
        }
    }

    function enableTakeoffBarButtons(){

        var tb$ = getTakeoffBar$();
        tb$.find('a.use-area.btn').click(onClickSendAreaOrPerim);
        tb$.find('a.use-perimeter.btn').click(onClickSendAreaOrPerim);
    }


    function copyTextToClipboard(text) {

        startProcess('Copying...');


        console.log('Copying to Clipboard: ' + text);

        var textArea = document.createElement("textarea");

        //
        // *** This styling is an extra step which is likely not required. ***
        //
        // Why is it here? To ensure:
        // 1. the element is able to have focus and selection.
        // 2. if the element was to flash render it has minimal visual impact.
        // 3. less flakyness with selection and copying which **might** occur if
        //    the textarea element is not visible.
        //
        // The likelihood is the element won't even render, not even a
        // flash, so some of these are just precautions. However in
        // Internet Explorer the element is visible whilst the popup
        // box asking the user for permission for the web page to
        // copy to the clipboard.
        //

        // Place in the top-left corner of screen regardless of scroll position.
        textArea.style.position = 'fixed';
        textArea.style.top = 0;
        textArea.style.left = 0;

        // Ensure it has a small width and height. Setting to 1px / 1em
        // doesn't work as this gives a negative w/h on some browsers.
        textArea.style.width = '2em';
        textArea.style.height = '2em';

        // We don't need padding, reducing the size if it does flash render.
        textArea.style.padding = 0;

        // Clean up any borders.
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';

        // Avoid flash of the white box if rendered for any reason.
        textArea.style.background = 'transparent';
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();

        try {
            var successful = document.execCommand('copy');
            var msg = successful ? 'successful' : 'unsuccessful';
            console.log('Copying text command was ' + msg);
        } catch (err) {
            console.log('Oops, unable to copy');
        }

        document.body.removeChild(textArea);

        setTimeout(endProcess,500,'Copying...');
    }



    // publics //////////////////////////////////////////////

    /**
     * Expose public functions
     */

    this.popUpHide			= popUpHide;
    this.popUpShow			= popUpShow;
    this.popUpHide2			= popUpHide2;
    this.popUpShow2			= popUpShow2;
    this.startProcess		= startProcess;
    this.endProcess			= endProcess;
    this.getProcessList		= getProcessList;
    this.countProcesses		= countProcesses;
    this.goToSheetList		= goToSheetListPage;
    this.goToSheetListPage	= goToSheetListPage;
    this.showTakeoffBar		= showTakeoffBar;
    this.hideTakeoffBar		= hideTakeoffBar;
    this.updateTakeoffBar	= updateTakeoffBar;
    this.isSpreadSheet		= function(){ return isSpreadSheet; };
    this.isSheetList		= function(){ return isSheetList;   };
    this.dismissTargetCell	= dismissTargetCell;
    this.handleDragOver		= handleDragOver;
    this.handleDragoUT		= handleDragOut;
    this.handleDnDFileSelect= handleDnDFileSelect;

    // init
    init();

    return this;
};// end main takeofflite class



/** get the grape city key for the current environment **/
function getGCKey() {
    var isDev = String(window.location).indexOf('dev.takeofflite.com') >= 0;
    var devKey = "dev.takeofflite.com,E976691285392285#B0hVwVFWlVUVwZVVnNWZkFHREV7UYRFN9V6MDtGeQpmTthXQnFkR6UTTTV6KKBFR9hEeZFWeqRneylTOTdHbZFzd8tCMKljazhHZqlHWhJkQalkb7o7QUNFVzBTS72kRxEmZIhnUH9mTsZEczZUb0hjQzsUM6tiWixWbh54KnVmSrkkSrJTYsRmYmlFbqVGMyJ4KBh5TzlDVlBTS8IWdhlGWoZUV0B7ThR7UTJjajZVRaBXYY3GeotWSlVlTPV6bYRHd4VVNpVTT9klVlp7arlVWyYTerdjMl94Y6ZFcYR7Z8FXSt3CTjh5ROdTUrlFdHRkYrMFSwgneXxET8EETiojITJCLigTRzITQCR4MiojIIJCLyITN5kDOyADN0IicfJye#4Xfd5nIV34M6IiOiMkIsIyMx8idgMlSgQWYlJHcTJiOi8kI1tlOiQmcQJCLiEzMzUTNwAiNwATMwIDMyIiOiQncDJCLiATMxETMyAjMiojIwhXRiwiIt36YuUGdpxmZm3WZrFGduYXZkJiOiMXbEJCLiMmbJBSZ4lGTgYmZvV6ahRlI0ISYONkIsUWdyRnOiwmdFJCLiUDOyITOzUDOyETO6YzN9IiOiQWSiwSflNHbhZmOiI7ckJye0ICbuFkI1pjIEJCLi4TPnBDczEja69meT5maId7LiNEM8g7Zq5UVuFzQ9d6Ur5WQSVnQYdHb8gzSyU6Q9pGcrJTSQpGOFVlT5UHVNhHcz3yTC36biNzVn94MqRGN8kENOhkdOxva5x";
    var prodKey = "Takeoff Lite Inc,app.takeofflite.com,619131854341579#B03pNboZzcHhlZTxkanlVVatGT8JGenlHVZNWYldneBNUdTRjQQ3yKhhjcOhna7cXcGt6KUhFeDljVLR7L78GMNlTW6kUTaNzZwFEZz2kbEZne5cTY4IWVQJXTXB5T58kdKRzTxZlRntCTGpleU96akVXN9UEZGdjehN6bFBDboRWUvFDU5k7YLVUTyZ7KGdXUrllbEVlYHVkbzpVU0dFSl3kT9ElZ93SWwhVNX3kaBRGaRFmN8Z5cDNnZ4sCaahVW6IXN5l6Mv4meIFjc8EnUTJTUwska73mVx3CdSlTWCdGSRFXOBd4dLpkN9BHSWZmI0IyUiwiIEZjQGJzN6MjI0ICSiwSM8ATOwcDO9cTM0IicfJye#4Xfd5nIV34M6IiOiMkIsIyMx8idgMlSgQWYlJHcTJiOi8kI1tlOiQmcQJCLikDN4MTNwAiMwATMwIDMyIiOiQncDJCLi46bj9SZ4lGbmZ6bltWY49CcwFmI0IyctRkIsIyYulEIlRXaMBiZm3WZrFGViojIh94QiwiI9cTNxQzM4UDOxMTM9EjNiojIklkIs4XZzxWYmpjIyNHZisnOiwmbBJye0ICRiwiI34TQrY5V0VTczk4VJdUM5tiMzkmUU3WbMJ6Svt6Sq3EMv2kVyA7KaZzaCx4btRUSaNHRmZXSMd5RFVmeyNXN7VmTIlDSXRTOrV4c5hFW5JXb89kMsJGZ9hlbMF4Sr86Mr3GRvI5Ls1Hd";
    var gcLicense = isDev ? devKey : prodKey;
    return gcLicense;
}


/// get the urls GET vars
function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function (m, key, value) {
        vars[key] = value;
    });
    return vars;
}

// convenience function to check if a variable is defined
function isUndefined(__var) {
    var isUn = true;
    try {
        var isUn = typeof __var === 'undefined'
            || __var === null
            || String(__var) === 'undefined';
    } catch (e) {
        console.log('Error checking for undefined variable: ' + __var, e);
        return true;
    }
    return isUn;
}


/**
 * A general error handler
 * @param {type} __err
 * @returns {undefined}
 */
function errorHandlerMisc(__err){

    console.log('Error: ', __err);
    //TODO: look for process name and end spinner process if there is one
}

/**
 * is dev env?
 * @returns {unresolved}
 */
function isDev(){
    return Boolean ( String(window.location).indexOf('dev.takeofflite.com') >= 0 );
}

/**
 * console log only in dev mode
 * @param {type} __msg
 * @param {type} __data
 * @returns {undefined}
 */
function trace(__msg,__data){
    if(isDev()){
        console.log(__msg,__data);
        trace = console.log;
    } else if(!traceConsoleMsg) {
        traceConsoleMsg = true;
        console.log('[TKL] Note: trace output disabled.');
    }
}
var traceConsoleMsg = false;


// runtime - wait for page and initialize app code
var takeOffLite;
$(document).ready(function () {
    console.log('Creating TakeOffLite JS App Instance...', url);

    window.TakeOffLite = new TakeOffLiteApp();
});
