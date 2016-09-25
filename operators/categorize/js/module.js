function countvalues(value) {
    resplist = responsekeys.split(/,/);
    cnt = 0;
    for (respid in resplist) {
        listobj = document.forms['categorizationform'].elements['cat_' + resplist[respid]];
        if (listobj.options[listobj.selectedIndex].value == value) {
            cnt++;
        }
    }
    return cnt;
}

function checkmaxrange(listobj) {
    if (maxitemspercategory && !allowmultiple) {
        if (countvalues(listobj.options[listobj.selectedIndex].value) > maxitemspercategory) {
               alert(message);
            listobj.selectedIndex = 0; 
            listobj.focus();
        }
    }
}
