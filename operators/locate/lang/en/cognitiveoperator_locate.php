<?php

$string['datarangeis'] = 'You must enter values in the range';
$string['blindness'] = 'Blindness';
$string['displaysize'] = 'Display dimension';
$string['height'] = 'Height';
$string['isinneighbourhood'] = '{$a} is/are in your neighbourhood';
$string['locate'] = '2D Cartography';
$string['locatesettings'] = 'Parameter settings for 2D cartography';
$string['mylocate'] = 'My map';
$string['neighbourhood'] = 'Neighbourhood radius';
$string['notconfigured'] = 'This module seems not having been configured.';
$string['organizinglocate'] = 'Location (2D) of the ideas';
$string['pluginname'] = 'Cognitive Operator : 2D Map Location';
$string['savelocations'] = 'Save coordinates';
$string['showlabels'] = 'Display labels';
$string['width'] = 'Width';
$string['xmaxrange'] = 'Max value (X)';
$string['xminrange'] = 'Min value (X)';
$string['xquantifier'] = 'X quantifier';
$string['ymaxrange'] = 'Max value (Y)';
$string['yminrange'] = 'Min value (Y)';
$string['yquantifier'] = 'Y quantifier';
$string['integer'] = 'Integer';
$string['float'] = 'Floating point';
$string['quantifiertype'] = 'Quantifier type';
$string['quantifiers'] = 'Quantifiers';
$string['detection'] = 'Detection';

$string['displaysize_help'] = '
<p><b>Parameter:</b> Display size</p>
<p>These parameters specify the graphical size of the map output in the display screen.</p>
';

$string['neighbourhood_help'] = '
<p><b>Parameter:</b> Neighbourhood radius</p>
<p>Setting this parameter to other than 0 will set the radius the "neighbourhood detector" will use when you submit location quantifiers. When a radius is set while submitting, the organize display will tell you how many answers from other users in group are in the neighbourhood of the location you gave.</p>
<p>If the neighbourhood setting is set to 0, no information is given to the user when preparing. Neither will information be given in any case in blindness mode. The only difference is that blindness control is only available to module\'s administrator.</p>
';

$string['quantifiers_help'] = '
<p><b>Parameter:</b> Quantifiers</p>
<p>The 2D cartography principle is to locate ideas on a map using two more or less independant quantifiers. These quantifying values may be issued from a measurement, or might have been given as estimation by participants.</p>
<p>These parameters allow you to name the quantifier used for both dimensions of the map.</p>
';

$string['quantifiertype_help'] = '
<p><b>Parameter:</b> Quantifyer type</p>
<p>You may specify what numeric type will be used for quantifying inputs. It may be an integer type which will not accept negative values, or floating point values that may be entered using scientific standard notation (e.g.: -3.5E+2)</p>
<p>Any value set outside the allowed range will be limited to the range bounds.</p>
';

$string['ranges_help'] = '
<p><b>Parameter:</b> Ranges</p>
<p>These four parameters specify the acceptable bounds of values for both the dimensions of the map.</p>
';

$string['showlabels_help'] = '
<p><b>Parameter:</b> Show labels</p>
<p>If enabled, the inputs are displayed as texts on the map, near the location point. If not, input texts can be viewed as help popups when passing over the location points.</p>
';
