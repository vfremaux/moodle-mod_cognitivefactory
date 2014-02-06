<?php

$string['addthreemore'] = 'Add three more fields';
$string['blindness'] = 'Blindness';
$string['allowcheckcycles'] = 'Allow cycle checking';
$string['clearconnections'] = 'Clear all mapping data';
$string['checkcycles'] = 'Show cycles';
$string['displaynormal'] = 'Display in normal mode';
$string['editmultiplemapentry'] = 'Edit a composite qualifier';
$string['fieldname'] = 'Name';
$string['fieldvalue'] = 'Value';
$string['gridediting'] = 'Grid editing';
$string['inputdata'] = 'Input data';
$string['onetoonerandom'] = 'Random pair comparison';
$string['picktwoandqualify'] = 'Pick pair and qualify';
$string['map'] = 'Map';
$string['mapofresponses'] = 'Mapping ideas';
$string['mapsettings'] = 'Parameter settings for the mapping';
$string['orderminusone'] = 'Lower order: {$a}';
$string['orderplusone'] = 'Higher order: {$a}';
$string['organizingmap'] = 'Mapping ideas together';
$string['pluginname'] = 'Cognitive Operator : Mapping';
$string['quantified'] = 'Quantify the connection';
$string['saveconnections'] = 'Save the connections';
$string['savemultiple'] = 'Save the composite qualifier';
$string['showmatrixproduct'] = 'Show pathes';
$string['toomuchdata'] = 'Too much data for this implementation of the operator (max={$a})';
$string['integer'] = 'Integer';
$string['float'] = 'Float';
$string['multiple'] = 'Composite';
$string['quantifiertype'] = 'Quantifier type';
$string['procedure'] = 'Procedure';
$string['procedure_help'] = 'You can choose the procedure to help you filling the map';

$string['checkcycles_help'] = '
<p><b>Parameter:</b> Allow checking cycles</p>

<p>When a set of elements are related, such relations may form cycles at first order (an element is self-related), second order, third order, etc.</p>

<p>When this mapping denotes "cause to effet" relationships, analyst can even assume there might be a "system" (Cf. Ludwig von Bertallanffy : General theory of systems ; Edgar Morin : Method). Searching cycles in an association map is a way to identify the eventual presence of such a system.</p>
';

$string['quantified_help'] = '
<p><b>Parameter:</b> Quantification</p>

<p>When disabled, the only thing that will be relevant in the operaotr is wether there is or there is not an association from a source idea to a target idea. Marking association is boolean.</p>
<p>When enabled, the participant may weight the association with:
<ul>
<li>an integer</li>
<li>a floating point number,</li>
<li>or either store a composite information set in the association.</li>
</ul>
</p>
';

$string['quantifiertype_help'] = '
<p><b>Parameter:</b> Quantifier type</p>

<p>You may specify what valuation type is required on the association. This valuation may be:
<ul>
<li><b>of integer type</b> which will not accept negative values,</li> 
<li><b>of floating point type</b> that will accept any floating point number including using scientific notation (e.g.: -3.5E+2)</li>
<li><b>composite</b> that will accept any list of named textual values.
</ul>
</p>
';