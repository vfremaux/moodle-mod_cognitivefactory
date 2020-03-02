<?php

$string['addthreemore'] = 'Ajouter trois champs';
$string['allowcheckcycles'] = 'Autoriser la vérification des cycles ';
$string['clearconnections'] = 'Effacer toutes les relations';
$string['checkcycles'] = 'Examiner les cycles';
$string['displaynormal'] = 'Afficher en mode normal';
$string['editmultiplemapentry'] = 'Entrée d\'un quantificateur multiple de relation';
$string['fieldname'] = 'Nom ';
$string['fieldvalue'] = 'Valeur ';
$string['gridediting'] = 'Edition par grille ';
$string['inputdata'] = 'Entrer des données ';
$string['onetoonerandom'] = 'Qualification de paires aléatoires ';
$string['picktwoandqualify'] = 'Choix d\'une paire et qualification  ';
$string['map'] = 'Mise en relation';
$string['mapofresponses'] = 'Carte des relations';
$string['mapsettings'] = 'Réglage des paramètres de la mise en relation';
$string['orderminusone'] = 'Ordre inférieur : {$a}';
$string['orderplusone'] = 'Ordre supérieur : {$a}';
$string['organizingmap'] = 'Mettre en relation des idées';
$string['noresponses'] = 'Pas de réponses';
$string['pluginname'] = 'Opérateur Cognitif : Carte des relations';
$string['quantified'] = 'Quantifier la relation ';
$string['saveconnections'] = 'Enregistrer les relations';
$string['savemultiple'] = 'Enregistrer les données';
$string['showmatrixproduct'] = 'Montrer les chemins';
$string['toomuchdata'] = 'Trop de données d\'entrée pour cette implémentation de l\'opérateur (max={$a})';
$string['integer'] = 'Entier';
$string['float'] = 'Flottant';
$string['multiple'] = 'Composite';
$string['quantifiertype'] = 'Type de quantifieur';
$string['procedure'] = 'Procédure';
$string['procedure_help'] = 'Procédure';

$string['checkcycles_help'] = '
<p><b>Paramètre :</b> Permettre la vérification des cycles</p>
<p>Lorsque un ensemble d\'éléments sont mis en relation deux à deux, il est possible que ces relations forment des boucles au premier ordre, deuxième ordre, troisième ordre, etc.</p>
<p>Lorsque cette relation est synonyme d\'une dépendance de "cause à effet", les scientifiques peuvent même identifier la présence d\'un "système" (Cf. Ludwig von Bertallanffy : Théorie des systèmes ; Edgar Morin : La méthode). La recherche de cycles permet d\'identifier la présence éventuelle de tels systèmes.</p>
';

$string['quantified_help'] = '
<p><b>Paramètre :</b> Quantification</p>
<p>Lorsque cette option est désactivée, seule la présence d\'une connexion entre une idée "source" et une idée "cible" intéresse l\'exercice. Le marquage est donc en tout ou rien (booléen). Il y a relation ou il n\'y a pas relation.</p>
<p>Lorsqu\'elle est activée, il est possible de "pondérer" cette relation par :
<ul>
<li>un nombre entier</li>
<li>ou réel, </li>
<li>ou encore, de stocker des informations multiples à l\'intersection des deux idées.</li>
</ul>
</p>

<p>Exemple d\'utilisation de la valuation multiple : soit un script de théâtre entre une dizaine d\'acteurs. Il est possible de trier dans cet opérateur l\'ensemble des dialogues d\'une scène et rassembler dans la matrice les différentes assertions de chaque personnage en direction d\'un autre personnage. Le dialogue entier peut être réparti dans les différentes relations entre les personnages.</p>
';

$string['quantifiertype_help'] = '
<p><b>Paramètre :</b> Type de la valuation de la relation</p>

<p>Il est possible de préciser de quelle nature est la valuation de la relation.Cette valuation peut être :
<ul>
<li><b>entière</b>, et ne pas accepter des valeurs négatives,</li> 
<li><b>réele</b> et accepter toute valeur à virgule ou écrite selon le format scientifique (ex : -3.5E+2)</li>
<li><b>composite</b>, et accepter une liste de taille quelconque de valeurs textuelles nommées.
</ul>
</p>
';