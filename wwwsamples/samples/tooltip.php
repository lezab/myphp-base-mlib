<?php
mlib\ui\tooltip\MTooltip::registerAssetsPath("mlib/assets/mlib/MTooltip");

mlib\ui\tooltip\MTooltip::printTooltipedContent("Un mot tooltipé", "Le contenu du tooltip");
$tt_span = mlib\ui\tooltip\MTooltip::getTooltipedContent("Un autre mot tooltipé", "Un autre contenu\navec plusieurs lignes\njuste pour l'exemple");
?>
<span style="float:right;"><?=$tt_span?></span>