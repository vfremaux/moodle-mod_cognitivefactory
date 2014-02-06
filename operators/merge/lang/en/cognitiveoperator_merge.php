<?php

$string['allowreducesource'] = 'Allow reducing the source';
$string['choicedata'] = 'Ideas sets';
$string['custommerge'] = 'Merge';
$string['filterlimitundefined'] = 'The max amount of ideas to keep is not defined.<br/> The configuration for this operator might not have been saved.';
$string['maxideasleft'] = 'Max number of ideas after merging';
$string['merge'] = 'Reduction by merging';
$string['mergedata'] = 'Merge data';
$string['mergeddata'] = 'Merged data';
$string['mergesettings'] = 'Parameter settings for merging ideas';
$string['mymerges'] = 'My merge';
$string['nomergeinprogress'] = 'No merge in progress';
$string['noothermerges'] = 'No more other merges in progress (other paricipants)';
$string['organizingmerge'] = 'Merge ideas into one to reduce ideas number'; 
$string['othermerges'] = 'Other participant\'s mergings';
$string['pluginname'] = 'Cognitive Operator : Reduction by merging';
$string['responsestokeep'] = 'idea(s) to keep';
$string['saveandreduce'] = 'Save and reduce the source';
$string['savemerges'] = 'Save the merges';
$string['sourcedata'] = 'Source data';

$string['mergings'] = 'merging records';

$string['allowreducesource_help'] = '
<p><b>Parameter:</b> Allow reduction of the source</p>

<p>When enabled, the participants are allowed to renew the source input set, deleting merged entries and adding the results of the merge. This may be usefull in a decision process where there are many contributors, or when a large amount of inputs were given.</p>
<p>Beware that this will delete all related data for the deleted entries and for all operators. This may perturbate the results of other operators.</p>
';

$string['maxideasleft_help'] = '
<h2>Operator: filter (by eliminating)</h2>
<p><b>Parameter:</b> Maximum number of ideas left</p>
<p>This parameter allows forcing participants to keep a certain amount of ideas from the source. This setting will determine how many "merging subgroups" will be deployed for grouping ideas and find a merge output for the subset.</p>
';
