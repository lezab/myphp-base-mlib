<?php
include(__DIR__."/resources/datas_for_mtree.php");

$mtree = new mlib\ui\tree\MTree($tree,  $labels);
$mtree->registerAssetsPath("mlib/assets/mlib/MTree");

// Par défault l'arbre est entièrement replié
$mtree->display();

// Si on veut que l'arbre soit ouvert directment à un endroit donné
// $mtree->display(6);
// Si on veut que l'arbre soit complètement ouvert
// $mtree->display(MTree::FULL_OPENED);
?>
<!-- SPLIT -->
<br><br>
Code de "resources/tree_datas.php" : <br>
<div class="code">
<?php
	highlight_file(__DIR__."/resources/datas_for_mtree.php");
?>
</div>