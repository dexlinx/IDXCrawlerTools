function reloadPage() {
    window.location.reload(true);
}

(function(document) {
	var last;
	/**
	 * Get full CSS path of any element
	 *
	 * Returns a jQuery-style CSS path, with IDs, classes and ':nth-child' pseudo-selectors.
	 *
	 * Can either build a full CSS path, from 'html' all the way to ':nth-child()', or a
	 * more optimised short path, stopping at the first parent with a specific ID,
	 * eg. "#content .top p" instead of "html body #main #content .top p:nth-child(3)"
	 */
	function cssPath(el) {
		var fullPath    = 0,  // Set to 1 to build ultra-specific full CSS-path, or 0 for optimised selector
		    useNthChild = 0,  // Set to 1 to use ":nth-child()" pseudo-selectors to match the given element
		    cssPathStr = '',
		    testPath = '',
		    parents = [],
		    parentSelectors = [],
		    tagName,
		    cssId,
		    cssClass,
		    tagSelector,
		    vagueMatch,
		    nth,
		    i,
		    c;

		// Go up the list of parent nodes and build unique identifier for each:
		while ( el ) {
			vagueMatch = 0;

			// Get the node's HTML tag name in lowercase:
			tagName = el.nodeName.toLowerCase();
			// Get node's ID attribute, adding a '#':
			cssId = ( el.id ) ? ( '#' + el.id ) : false;

			// Get node's CSS classes, replacing spaces with '.':
			cssClass = ( el.className ) ? ( '.' + el.className.replace(/\s+/g,".") ) : '';

			// Build a unique identifier for this parent node:
			if ( cssId ) {
				// Matched by ID:
				tagSelector = tagName + cssId + cssClass;
			} else if ( cssClass ) {
				// Matched by class (will be checked for multiples afterwards):
				tagSelector = tagName + cssClass;
			} else {
				// Couldn't match by ID or class, so use ":nth-child()" instead:
				vagueMatch = 1;
				tagSelector = tagName;
			}

			// Add this full tag selector to the parentSelectors array:
			parentSelectors.unshift( tagSelector )

			// If doing short/optimised CSS paths and this element has an ID, stop here:
			if ( cssId && !fullPath )
				break;

			// Go up to the next parent node:
			el = el.parentNode !== document ? el.parentNode : false;

		} // endwhile

		// Build the CSS path string from the parent tag selectors:
		for ( i = 0; i < parentSelectors.length; i++ ) {

			cssPathStr += ' ' + parentSelectors[i];// + ' ' + cssPathStr;

			// If using ":nth-child()" selectors and this selector has no ID / isn't the html or body tag:
			if ( useNthChild && !parentSelectors[i].match(/#/) && !parentSelectors[i].match(/^(html|body)$/) ) {

				// If there's no CSS class, or if the semi-complete CSS selector path matches multiple elements:
				if ( !parentSelectors[i].match(/\./) || $( cssPathStr ).length > 1 ) {

					// Count element's previous siblings for ":nth-child" pseudo-selector:
					for ( nth = 1, c = el; c.previousElementSibling; c = c.previousElementSibling, nth++ );

					// Append ":nth-child()" to CSS path:
					cssPathStr += ":nth-child(" + nth + ")";

				}
			}

		}

		// Return trimmed full CSS path:
		return cssPathStr.replace(/^[ \t]+|[ \t]+$/, '');
	}


	/**
	 * MouseOver action for all elements on the page:
	 */
	function inspectorMouseOver(e) {
		// NB: this doesn't work in IE (needs fix):
		var element = e.target;

		// Set outline:
		element.style.outline = '2px solid #6600CC';

		// Set last selected element so it can be 'deselected' on cancel.
		last = element;
	}


	/**
	 * MouseOut event action for all elements
	 */
	function inspectorMouseOut(e) {
		// Remove outline from element:
		e.target.style.outline = '';
	}


	/**
	 * Click action for hovered element
	 */
	function inspectorOnClick(e) {
		e.preventDefault();

//============================================================================//
//============= THIS IS WHERE WE DO THE SPLIT WORK============================//
//============================================================================//
//Set VAR to for following test
var testMarkup = document.documentElement.outerHTML;
var testIdxStart = testMarkup.indexOf("idxstart");


//Only Run Split Once
//------------------------------------------------------------------------------
if (testIdxStart < 1){ // Has Split Run?

var verifyPath = cssPath(e.target);//Store the CSS Path in a var to verify
var cleanPath = verifyPath.replace(/\.\s+|\s\.|\.{2,}/g, "\.");//Remove any extra . in the string

if (document.querySelector(cleanPath) === null){
  var finalPath = cleanPath.replace(/\.([^.]*)$/,""); //Path Not Found, Remove last element
  console.log("NULL FOOL");
}else{
  console.log("NOT NULL");
  var finalPath = cleanPath //Path Was found
}

//This helps us get the split
var articleDiv = document.querySelector(finalPath); //Select the element so we can update it

//If We can't find the element, don't do the split
if(articleDiv != null){
  articleDiv.innerHTML = "idxstart idxstop";//Insert lookup text in selected div
}

document.scripts.namedItem("splitScript").remove();
    var html = document.documentElement.outerHTML;

//console.log("----------------------------- html ------------------------------")
//console.log(html);

//Some Final Cleanup
//------------------------------------------------------------------------------

        var removeOutline = html.replace(/outline:.*102,\s0,\s204\)\;/g, ""); //REMOVE: Outline

//------------------------------------------------------------------------------
var finalHTML = removeOutline;// This is the adjusted HTML to split

//console.log("------------------------ finalHTML ------------------------------")
//console.log(finalHTML);

if(articleDiv != null){
    //Get Header HTML
    var startIndex = finalHTML.indexOf("idxstart");
    var headerHTML = finalHTML.substring(0,startIndex);

    //Get Footer HTML
    var idxStopIndex = finalHTML.indexOf("idxstop");
    var lastIndex = finalHTML.indexOf("</html>");
    var footerHTML = finalHTML.substring(idxStopIndex+7,lastIndex+7);
}

// write the buttons and the content of header and footer
var myStyle = '<style>xmp {padding:10px; box-sizing:border-box; -moz-box-sizing:border-box; webkit-box-sizing:border-box; display:block; white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; width:830px; height:250px; margin-left:230px; overflow-x:auto;background: #EFEFEF none repeat scroll 0% 0%;border-style: solid;}</style>';

var headerDivStart = '<button class="js-headercopybtn" style="top:20px;">Copy Header</button> <button class="js-footercopybtn" style="top:20px;">Copy footer</button> <button onclick="reloadPage()" style="top:20px;">Select Again</button><center>Header HTML:</center><div class="js-headerDiv"><xmp>';
var headerDivEnd = '</xmp></div>';
var headerDivContent = headerHTML;

// This section prints out the FOOTER content and the button to copy it
var footerDivStart = '<center>Footer HTML:</center><div class="js-footerDiv"><xmp>';
var footerDivEnd = '</xmp></div>';
var footerDivContent = footerHTML;

//If I can do the split I'll spit that out, if Not I'll still spit out the updated HTML
if(articleDiv != null){
document.body.innerHTML = myStyle+headerDivStart+headerDivContent+headerDivEnd+footerDivStart+footerDivContent+footerDivEnd;
}else{
var headerDivStartNoSplit = '<button class="js-headercopybtn" style="top:20px;">Copy HTML Source</button> <button onclick="reloadPage()" style="top:20px;">Select Again</button><center>Split Not Available - But Did the Rest of the Work...<br /> FULL HTML:</center><div class="js-headerDiv"><xmp>';
var headerDivContentNoSplit = finalHTML;
document.body.innerHTML = myStyle+headerDivStartNoSplit+headerDivContentNoSplit+headerDivEnd;
}

// -------------------------HEADER BUTTON
var copyEmailBtn = document.querySelector('.js-headercopybtn');
copyEmailBtn.addEventListener('click', function(event) {
  // Select the email link anchor text
  var emailLink = document.querySelector('.js-headerDiv');
  var range = document.createRange();
  range.selectNode(emailLink);
  window.getSelection().addRange(range);

  try {
    // Now that we've selected the anchor text, execute the copy command
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Copy Header command was ' + msg);
    alert('Header Code Copied To Clipboard');
  } catch(err) {
    console.log('Oops, unable to copy');
  }

  // Remove the selections - NOTE: Should use
  // removeRange(range) when it is supported
  window.getSelection().removeAllRanges();
});

if(articleDiv != null){
  // -------------------------FOOTER BUTTON
var copyEmailBtn = document.querySelector('.js-footercopybtn');
copyEmailBtn.addEventListener('click', function(event) {
  // Select the email link anchor text
  var emailLink = document.querySelector('.js-footerDiv');
  var range = document.createRange();
  range.selectNode(emailLink);
  window.getSelection().addRange(range);

  try {
    // Now that we've selected the anchor text, execute the copy command
    var successful = document.execCommand('copy');
    var msg = successful ? 'successful' : 'unsuccessful';
    console.log('Copy Footer command was ' + msg);
    alert('FOOTER Code Copied To Clipboard');
  } catch(err) {
    console.log('Oops, unable to copy');
  }

  // Remove the selections - NOTE: Should use
  // removeRange(range) when it is supported
  window.getSelection().removeAllRanges();
});
}



//This is what kills the selector
var keyboardEvent = document.createEvent("KeyboardEvent");
		var initMethod = typeof keyboardEvent.initKeyboardEvent !== 'undefined' ? "initKeyboardEvent" : "initKeyEvent";
		keyboardEvent[initMethod](
		   "keydown", // event type : keydown, keyup, keypress
			true, // bubbles
			true, // cancelable
			window, // viewArg: should be window
			false, // ctrlKeyArg
			false, // altKeyArg
			false, // shiftKeyArg
			false, // metaKeyArg
			27, // keyCodeArg : unsigned long the virtual key code, else 0
			0 // charCodeArgs : unsigned long the Unicode character associated with the depressed key, else 0
		);
		document.dispatchEvent(keyboardEvent);
}

    //============================================================================//
    //============================================================================//
    //============================================================================//

		return false;
	}


	/**
	 * Function to cancel inspector:
	 */
   function inspectorCancel(e) {
   		// Unbind inspector mouse and click events:
   		if (e === null && event.keyCode === 27) { // IE (won't work yet):
   			document.detachEvent("mouseover", inspectorMouseOver);
   			document.detachEvent("mouseout", inspectorMouseOut);
   			document.detachEvent("click", inspectorOnClick);
   			document.detachEvent("keydown", inspectorCancel);
   			last.style.outlineStyle = 'none';
   		} else if(e.which === 27) { // Better browsers:
   			document.removeEventListener("mouseover", inspectorMouseOver, true);
   			document.removeEventListener("mouseout", inspectorMouseOut, true);
   			document.removeEventListener("click", inspectorOnClick, true);
   			document.removeEventListener("keydown", inspectorCancel, true);

   			// Remove outline on last-selected element:
   			last.style.outline = 'none';
   		}
   	}


	/**
	 * Add event listeners for DOM-inspectorey actions
	 */
	if ( document.addEventListener ) {
		document.addEventListener("mouseover", inspectorMouseOver, true);
		document.addEventListener("mouseout", inspectorMouseOut, true);
		document.addEventListener("click", inspectorOnClick, true);
		document.addEventListener("keydown", inspectorCancel, true);
	} else if ( document.attachEvent ) {
		document.attachEvent("mouseover", inspectorMouseOver);
		document.attachEvent("mouseout", inspectorMouseOut);
		document.attachEvent("click", inspectorOnClick);
		document.attachEvent("keydown", inspectorCancel);
	}

})(document);
