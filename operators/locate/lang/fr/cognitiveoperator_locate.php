<?php

$string['datarangeis'] = 'Entrez des données dans l\'intervalle';
$string['displaysize'] = 'Dimensions de visualisation ';
$string['height'] = 'Hauteur ';
$string['isinneighbourhood'] = '{$a} utilisateur(s) est/sont dans votre voisinage';
$string['locate'] = 'Cartographie';
$string['locatesettings'] = 'Réglage des paramètres de cartographie';
$string['mylocate'] = 'Mon placement';
$string['notconfigured'] = 'Ce module ne semble pas avoir été configuré ';
$string['neighbourhood'] = 'Rayon de voisinage ';
$string['organizinglocate'] = 'Localisation (2D) des entrées';
$string['pluginname'] = 'Opérateur Cognitif : Localisation 2D';
$string['savelocations'] = 'Enregistrer les coordonnées ';
$string['showlabels'] = 'Afficher les textes ';
$string['width'] = 'Largeur ';
$string['xmaxrange'] = 'Valeur maximale en X ';
$string['xminrange'] = 'Valeur minimale en X ';
$string['xquantifier'] = 'Quantificateur en abscisses (X) ';
$string['ymaxrange'] = 'Valeur maximale en Y ';
$string['yminrange'] = 'Valeur minimale en Y ';
$string['yquantifier'] = 'Quantificateur en ordonnées (Y) ';
$string['integer'] = 'Entier';
$string['float'] = 'Flottant';
$string['quantifiertype'] = 'Type de quantification';
$string['quantifiers'] = 'Quantifieurs';
$string['detection'] = 'Detection';

$string['displaysize_help'] = '
<p><b>Paramètre :</b> Dimensions de visualisation</p>
<p>Ces paramètres permettent de définir la largeur et hauteur graphique en pixels du graphe produit dans la page de visualisation.</p>
';

$string['neighbourhood_help'] = '
<p><b>Paramètre :</b> Rayon de voisinage</p>
<p>Si ce rayon de voisinage n\'est pas nul, le mode visible affichera le nombre d\'entrées dans le voisinage des valeurs données lors de la quantification.</p>
<p>Pour désactiver ces indications, réglez ce paramètre à 0. L\'effet sera identique que l\'usage du mode "isolé", à la différence que le mode isolé ne peut être mise en service que par l\'administrateur du module et non par les préparateurs.</p>
';

$string['quantifiers_help'] = '
<p><b>Paramètre :</b> Quantificateurs</p>
<p>Le principe d\'une cartographie 2D est de positionner les idées sur un plan, en utilisant deux critères quantifiables plus ou moins indépendants. Ces critères peuvent être issus d\'une mesure, ou d\'une estimation de poids donnée par les participants.</p>
<p>Ces paramètres vous permettent de donner le "nom" des quantificateurs utilisés pour chacune des dimensions.</p>
';

$string['quantifiertype_help'] = '
<p><b>Paramètre :</b> Type des quantificateurs</p>
<p>Il est possible de préciser de quelle nature numérique sont les quantificateurs. Ils peuvent être entiers, et n\'accepteront pas des valeurs négatives, ou réels et acceptent toute valeur à virgule ou écrite selon le format scientifique (ex : -3.5E+2)</p>
<p>Toute valeur en dehors des plages limites définies par les paramètres d\'intervalle sera "bridée" aux valeurs limites.</p>
';

$string['ranges_help'] = '
<p><b>Paramètre :</b> Intervalles de validité</p>
<p>Ces quatre paramètres permettent de définir les bornes acceptables pour les valeurs de quantification selon chacune des dimensions de la carte.</p>
';

$string['showlabels_help'] = '
<p><b>Paramètre :</b> Afficher les textes</p>
<p>Si cette option est active, les textes des idées sont affichées en clair sur la carte. Sinon, les textes sont visibles comme "bulles" lors du passage de la souris sur les positions sur la carte.</p>
';