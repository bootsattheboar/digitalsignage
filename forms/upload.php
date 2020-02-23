<?php require '../SignageOptions.php'; ?>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<title>Upload Slide</title>
        <script type="text/javascript"><!--
        var number_of_screens = <?php echo $options['num_screens']; ?>;
        
        var today = new Date();
        var miliseconds_in_day = 86400000;
        var expDate = new Date(today.getTime() + miliseconds_in_day * 14);
        var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        var NUM_YEARS = 5;
        var ordinals_special = ['zeroth','first', 'second', 'third', 'fourth', 'fifth', 'sixth', 'seventh', 'eighth', 'ninth', 'tenth', 'eleventh', 'twelfth', 'thirteenth', 'fourteenth', 'fifteenth', 'sixteenth', 'seventeenth', 'eighteenth', 'nineteenth'];
        var ordinals_deca = ['twent', 'thirt', 'fort', 'fift', 'sixt', 'sevent', 'eight', 'ninet'];
	    var screen_designation = "Screen";  //Combined with ordinals to indicate what screen user is posting to.  Typically "Screen" or "Floor".

        function initForm(theform) {
            //Attach screen selection options to screens_layer.
            if (number_of_screens > 1) {
                var newscreen = document.createElement("input");
                newscreen.type = "checkbox";
                newscreen.name = newscreen.id = "allscreens";
                newscreen.value = 1;
                newscreen.checked = true;
                newscreen.onclick = function () { selectAllScreens(document.theform); };

                var newlabel = document.createElement("label")
                newlabel.htmlFor = "allscreens"
                newlabel.innerHTML = "All " + capitalizeFirstChar(screen_designation) + "s";

                var el = document.getElementById("screens_layer");
                el.appendChild(newscreen);
                el.appendChild(newlabel);
                el.appendChild(document.createElement("br"));
            }
            for (numScreen = 1; numScreen <= number_of_screens; numScreen++) {
                var newscreen = document.createElement("input");
                newscreen.type = "checkbox";
                newscreen.name = newscreen.id = "screen" + numScreen;
                newscreen.value = 1;
                newscreen.checked = true;

                var newlabel = document.createElement("label")
                newlabel.htmlFor = "screen" + numScreen;
                newlabel.innerHTML = capitalizeFirstChar(ordinalsNumber(numScreen)) + " " + capitalizeFirstChar(screen_designation);

                var el = document.getElementById("screens_layer");
                el.appendChild(newscreen);
                el.appendChild(newlabel);
                el.appendChild(document.createElement("br"));
            }
            splitScreenClicked(theform);
            //Add some options to the Years drop-down.
            var thisYear = today.getFullYear();
            for (numYears = 0; numYears < NUM_YEARS; numYears++) {
                theform.exp_year.options[numYears] = new Option(thisYear, thisYear, false, false);
                thisYear++;
            }
            theform.exp_month.selectedIndex = expDate.getMonth();
            theform.exp_date.selectedIndex = expDate.getDate() - 1;
            dateChanged(expDate, theform.exp_day, theform.exp_month, theform.exp_date, theform.exp_year);
            //Attach event handler to screen checkboxes to deselect allscreens when one floor is clicked.
            for (i = 0; i < theform.elements.length; i++) {
                if (theform.elements[i].type == "checkbox" && theform.elements[i].name.indexOf("screen") > -1 && theform.elements[i].name != "allscreens") {
                    theform.elements[i].onclick = function(theevent) {
                        document.theform.allscreens.checked = false;
                    }
                }
            }
            tabIndexify(theform);
            enable(theform);
        }

        function enable(theform) {
            theform.resetButton.style.visibility = "hidden";
            theform.submitButton.value = "Upload";
            theform.submitButton.disabled = false;
        }

        function disable(theform) {
            theform.submitButton.disabled = true;
            theform.submitButton.value = "Please wait...";
            theform.resetButton.style.visibility = "visible";
        }

        function validate(theform) {
            disable(theform);
            if (areFilesCorrect(theform) && isDate(theform) && isScreenChecked(theform)) {
                theform.submit();
            } else {
                enable(theform);
            }
        }

        function areFilesCorrect(theform) {
            if (theform.left_file.value == "" && theform.left_url.value == "") {
                alert("You did not specify any file or URL to upload.\n\nPlease try again.");
                return false;
            }
            if (theform.right_file.value == "" && theform.right_url.value == "") {
                if (theform.split_slide.checked) {
                    alert("You indicated that you wish to upload two files or URLs, but you did not specify a right file/URL.\n\nPlease try again.");
                    return false;
                }
            }
            return true;
        }

        function isDate(theform) {
            if (expDate < today) {
                alert("The expiration date must be later than today.\n\nPlease change the expiration date.");
                theform.exp_date.focus();
                return false;
            }
            return true;
        }

        function isScreenChecked(theform) {
            for (i = 1; i < 7; i++) {
                if (document.getElementById("screen" + i).checked) {
                    return true;
                }
            }
            alert("You need to select at least one screen on which to display this slide.")
            return false;
        }

        function splitScreenClicked(theform) {
            if (theform.split_slide.checked) {
                show("right_layer");
            } else {
                hide("right_layer");
                theform.right_file.value = "";
            }
            urlUploadClicked(theform);
        }

        function urlUploadClicked(theform) {
            if (theform.left_url_upload.checked) {
                hide("left_file");
                show("left_url_layer");
            } else {
                show("left_file");
                hide("left_url_layer");
            }
            
            if (theform.split_slide.checked && theform.right_url_upload.checked) {
                hide("right_file");
                show("right_url_layer");
            } else if (theform.split_slide.checked) {               
                show("right_file");
                hide("right_url_layer");
            }
        }

        function neverExpireClicked(theform) {
            if (theform.never_expire.checked) {
                hide("expiration_layer");
            } else {
                show("expiration_layer");
            }
        }

        function show(el) {
            document.getElementById(el).style.display = "block";
        }

        function hide(el) {
            document.getElementById(el).style.display = "none";
        }

        function dateChanged(jsdateOb, dayOb, monthOb, dateOb, yearOb) {
            resizeDate(dayOb, monthOb, dateOb, yearOb);
            jsdateOb.setFullYear(yearOb.value, monthOb.selectedIndex, dateOb.selectedIndex + 1);
            dayOb.value = dayNames[jsdateOb.getDay()];
        }

        function resizeDate(dayOb, monthOb, dateOb, yearOb) {
            switch (monthOb.value) {
                case "02":
                    if (isLeapYear(yearOb.value)) {
                        if (dateOb.selectedIndex > 28) {
                            dateOb.selectedIndex = 28;
                        }
                    } else {
                        if (dateOb.selectedIndex > 27) {
                            dateOb.selectedIndex = 27;
                        }
                    }
                    dateOb.options[30] = null;
                    dateOb.options[29] = null;
                    if (isLeapYear(yearOb.value)) {
                        if (dateOb.options[28] == null) {
                            dateOb.options[28] = new Option("29", "29", false, false);
                        }
                    } else {
                        dateOb.options[28] = null;
                    }
                    break;
                case "01":
                case "03":
                case "05":
                case "07":
                case "08":
                case "10":
                case "12":
                    if (dateOb.options[28] == null) {
                        dateOb.options[28] = new Option("29", "29", false, false);
                    }
                    if (dateOb.options[29] == null) {
                        dateOb.options[29] = new Option("30", "30", false, false);
                    }
                    if (dateOb.options[30] == null) {
                        dateOb.options[30] = new Option("31", "31", false, false);
                    }
                    break;
                case "04":
                case "06":
                case "09":
                case "11":
                    if (dateOb.options[28] == null) {
                        dateOb.options[28] = new Option("29", "29", false, false);
                    }
                    if (dateOb.options[29] == null) {
                        dateOb.options[29] = new Option("30", "30", false, false);
                    }
                    if (dateOb.selectedIndex > 29) {
                        dateOb.selectedIndex = 29;
                    }
                    dateOb.options[30] = null;
                    break;
                default:
                    alert("Error: resizeDate failed.");
                    break;
            }
        }

        function isLeapYear(year) {
            return ((year % 4) == 0 && (year % 100) != 0) || ((year % 400) == 0);
        }

        function setRadioButton(radioGroup, checkedValue) {
            for (var i = 0; i < radioGroup.length; i++) {
                radioGroup[i].checked = false;
                if (radioGroup[i].value == checkedValue) {
                    radioGroup[i].checked = true;
                }
            }
        }

        function getRadioValue(radioGroup) {
            var radioLength = radioGroup.length;
            if (radioLength == undefined) {
                if (radioGroup.checked) {
                    return radioGroup.value;
                } else {
                    return "";
                }
            }
            for (var i = 0; i < radioLength; i++) {
                if (radioGroup[i].checked) {
                    return radioGroup[i].value;
                }
            }
            return "";
        }

        function selectAllScreens(theform) {
            if (theform.allscreens.checked) {
                for (i = 0; i < theform.elements.length; i++) {
                    if (theform.elements[i].type == "checkbox" && theform.elements[i].name.indexOf("screen") > -1) {
                        theform.elements[i].checked = true;
                    }
                }
            } else {
                for (i = 0; i < theform.elements.length; i++) {
                    if (theform.elements[i].type == "checkbox" && theform.elements[i].name.indexOf("screen") > -1) {
                        theform.elements[i].checked = false;
                    }
                }
            }
        }

        function tabIndexify(theform) {
            for (i = 0; i < theform.elements.length; i++) {
                if (theform.elements[i].name.indexOf("exp_day") < 0) {
                    theform.elements[i].tabIndex = i + 1;
                } else {
                    theform.elements[i].tabIndex = 0;
                }
            }
        }

        function ordinalsNumber(n) {
            if (n < 20) return ordinals_special[n];
            if (n%10 === 0) return ordinals_deca[Math.floor(n/10) - 2] + 'ieth';
           
            return ordinals_deca[Math.floor(n/10) - 2] + 'y-' + ordinals_special[n % 10];
        }

        function capitalizeFirstChar(s) {
            return s.charAt(0).toUpperCase() + s.slice(1);
        }

        // Disable form submission upon depression of the Return key
        function checkCR(evt) {
            var evt = (evt) ? evt : ((event) ? event : null);
            var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
            if ((evt.keyCode == 13) && (node.type == "text")) {
                return false;
            }
        }
        // Register checkCR event handler.
        document.onkeypress = checkCR;
        //-->
		</script>
	</head>
	<body onload="javascript:initForm(document.theform);">
		<form id="theform" action="<?php echo $options['urls']['upload']; ?>" method="post" name="theform" enctype="multipart/form-data">
			<div id="upload_options">
				<input onclick="javascript:splitScreenClicked(document.theform);" id="split_slide" name="split_slide" value="1" type="checkbox" />
				<label for="split_slide">Upload two files/URLs and keep them together.</label>
			</div>
            <div id="leftlayer" style="float:left; margin-right:50px;">
                <input onclick="javascript:urlUploadClicked(document.theform);" id="left_url_upload" name="left_url_upload" value="1" type="checkbox" />
				<label for="left_url_upload">Upload URL rather than a file.</label>
				<input id="left_file" name="left_file" size="40" type="file" />
				<div id="left_url_layer">
					<label for="left_url">URL:</label>
					<input id="left_url" name="left_url" size="40" type="text" />
                </div>
            </div>
            <div id="right_layer">
                <input onclick="javascript:urlUploadClicked(document.theform);" id="right_url_upload" name="right_url_upload" value="1" type="checkbox" />
				<label for="right_url_upload">Upload URL rather than a file.</label>
                <input id="right_file" name="right_file" size="40" type="file" />
                <div id="right_url_layer">
                    <label for="right_url">URL:</label>
                    <input id="right_url" name="right_url" size="40" type="text" />
                </div>
            </div>
            <div id="screens_layer" style="clear:both">
                Post to:<br />
            </div>
            <div id="expiration_layer">
                <label id="expdate_label" for="exp_month">Expiration date:</label>
                <select id="exp_month" name="exp_month" size="1"
                    onchange="javascript:dateChanged(expDate, document.theform.exp_day, document.theform.exp_month, document.theform.exp_date, document.theform.exp_year);">
                    <option value="01">January</option>
                    <option value="02">February</option>
                    <option value="03">March</option>
                    <option value="04">April</option>
                    <option value="05">May</option>
                    <option value="06">June</option>
                    <option value="07">July</option>
                    <option value="08">August</option>
                    <option value="09">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <select id="exp_date" name="exp_date" size="1"
                    onchange="javascript:dateChanged(expDate, document.theform.exp_day, document.theform.exp_month, document.theform.exp_date, document.theform.exp_year);">
                    <option value="01">1</option>
                    <option value="02">2</option>
                    <option value="03">3</option>
                    <option value="04">4</option>
                    <option value="05">5</option>
                    <option value="06">6</option>
                    <option value="07">7</option>
                    <option value="08">8</option>
                    <option value="09">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                    <option value="13">13</option>
                    <option value="14">14</option>
                    <option value="15">15</option>
                    <option value="16">16</option>
                    <option value="17">17</option>
                    <option value="18">18</option>
                    <option value="19">19</option>
                    <option value="20">20</option>
                    <option value="21">21</option>
                    <option value="22">22</option>
                    <option value="23">23</option>
                    <option value="24">24</option>
                    <option value="25">25</option>
                    <option value="26">26</option>
                    <option value="27">27</option>
                    <option value="28">28</option>
                    <option value="29">29</option>
                    <option value="30">30</option>
                    <option value="31">31</option>
                </select>
                <select id="exp_year" name="exp_year" size="1" onchange="javascript:dateChanged(expDate, document.theform.exp_day, document.theform.exp_month, document.theform.exp_date, document.theform.exp_year);"></select>
                <label id="expday_label" for="exp_day">Day:</label>
                <input id="exp_day" name="exp_day" readonly="readonly" size="11" type="text" />
                <label id="exptime_label" for="exp_hour">Expiration time:</label>
                <select id="exp_hour" name="exp_hour" size="1" tabindex="25">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6" selected="selected">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                </select>
                <label id="exptime_separator_label" for="exp_minute">:</label>
                <select id="exp_minute" name="exp_minute" size="1" tabindex="26">
                    <option selected="selected" value="00">00</option>
                    <option value="05">05</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                    <option value="30">30</option>
                    <option value="35">35</option>
                    <option value="40">40</option>
                    <option value="45">45</option>
                    <option value="50">50</option>
                    <option value="55">55</option>
                </select>
                <select id="exp_meridian" name="exp_meridian" size="1" tabindex="27">
                    <option value="am">AM</option>
                    <option value="pm" selected="selected">PM</option>
                </select>
            </div>
            <div id="never_expire_layer">
                <input onclick="javascript:neverExpireClicked(document.theform);" name="never_expire" value="1" type="checkbox" />
                <label for="never_expire">Never expire. I will manually remove.</label>
            </div>
			<div id="submit_button">
				<input id="submitButton" onclick="javascript:validate(document.theform);" name="submitButton" value="Upload" type="button" />
				<input id="resetButton" class="hidden" onclick="javascript:enable(document.theform);" name="resetButton" value="Reset Upload Button" type="button" />
			</div>
		</form>
	</body>
</html>