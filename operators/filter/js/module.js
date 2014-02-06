function initChecks(){
    for(i = 0 ; i < total ; i++){
        if ($('#sel_' + i).attr('checked')){
            checks[i] = 1;
        } else {
            checks[i] = 0;
        }
    }
}

function countChecks(){
    checkcount = 0;
    for(i = 0 ; i < total ; i++){
        checkcount += checks[i];
    }
    return checkcount;
}

function toggleCheck(checkobj, ix){
    if (checkobj.checked == true){
        checks[ix] = 1;
        $('#tdc_' + ix).attr('class', 'cognitiveoperator-filter-kept');
        $('#tdr_' + ix).attr('class', 'cognitiveoperator-filter-kept');
    } else {
        checks[ix] = 0;
        $('#tdc_' + ix).attr('class', 'cognitiveoperator-filter-deleted');
        $('#tdr_' + ix).attr('class', 'cognitiveoperator-filter-deleted');
    }
    initChecksStates();
}

function initChecksStates(){
    initChecks();

    realleft = countChecks();
    $('#leftcount').html('' + realleft - maxleft);
    if (realleft <= maxleft){
		if (!candeletemore){
	        lockChecked();
		}
        $('#go1').attr('disabled', false);
        $('#go2').attr('disabled', false);
    } else {
        unlockAll();
        $('#go1').attr('disabled', true);
        $('#go2').attr('disabled', true);
    }
}

/**
* locks real checkboxes, while transferring values to shadows
*/
function lockChecked(){
    for(i = 0 ; i < total ; i++){
        if ($('#sel_' + i).attr('checked')){
            $('#shadow_' + i).attr('value', 1);
        	$('#sel_' + i).attr('disabled', true);
        } else {
            $('#shadow_' + i).attr('value', 0);
        }
    }
}

/**
* unlock and set back real values from shadow
*
*/
function unlockAll(){
    for(i = 0 ; i < total ; i++){
        if ($('#shadow_' + i).attr('checked')){
            $('#sel_' + i).attr('value', 1);
        } else {
            $('#sel_' + i).attr('value', 0);
        }
		$('#sel_' + i).attr('disabled', false)
    }
}
