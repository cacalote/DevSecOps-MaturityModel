<?php
$dimensions = array();

$files = scandir("data");
foreach ($files as $file) {
    if(strlen($file) > 2)
    include_once "data/$file";
}
ksort($dimensions);
foreach ($dimensions as $dimensionName => $subDimension) {
    ksort($subDimension);
    foreach ($subDimension as $subDimensionName => $elements) {
        $newElements = $elements;
        ksort($newElements);
        $dimensions[$dimensionName][$subDimensionName] = $newElements;
    }
}

function getEaseOfImplementation($dimensions, $elementImplementation)
{
    if($elementImplementation == null) {
        return ;
    }
    $knowledge = $elementImplementation['easeOfImplementation']["knowledge"];
    $knowledge = getKnowledge($dimensions, $elementImplementation);


    $value = $knowledge + $elementImplementation['easeOfImplementation']["time"] * 2 + $elementImplementation['easeOfImplementation']["resources"];
    $value = $value / 4;

    if (array_key_exists('dependsOn', $elementImplementation) && $_GET['aggregated'] == "true") {
        foreach ($elementImplementation['dependsOn'] as $dependency) {
            $dependencyElement = getElementByName($dimensions, $dependency);
            $value += getEaseOfImplementation($dimensions, $dependencyElement);
        }
    }
    if ($value > 5) {
        $value = 5;
    }

    return number_format((float)$value, 2, '.', '');
}

function getKnowledge($dimensions, $elementImplementation)
{
    $knowledge = $elementImplementation['easeOfImplementation']["knowledge"];
    if (is_array($knowledge)) {
        $sum = 0;
        // areas = operation, development, expertise, security
        $areaCount = 4;
        foreach ($knowledge as $knowledgeAttribute) {
            $sum += $knowledgeAttribute;
        }
        $knowledge = $sum / $areaCount;
    }

    return $knowledge;
}


function build_table_tooltip($array, $headerWeight = 2)
{
    $mapKnowLedge = array("Sehr wenig (eine Disziplin)", "Wenig (eine Disziplin)", "Mittel (zwei Disziplinen)", "Viel (zwei Disziplinen)", "Sehr viel (drei oder mehr Disziplinen)");
    $mapTime = array("Sehr wenig (1-2 Tage)", "Wenig (eine Woche)", "Mittel (zwei Wochen)", "Viel (drei Wochen)", "Sehr viel (drei oder mehr Wochen)");
    $mapResources = array("Sehr wenig", "Wenig", "Mittel", "Viel", "Sehr viel");
    $mapUsefulness = array("Sehr wenig", "wenig", "mittel", "hoch", "sehr hoch");

    $html = "";
    $html .= "<h" . $headerWeight . ">Risiken und Maßnahmen</h$headerWeight>";
    $html .= "<div><b>Risiko:</b> " . $array['risk'] . "</div>";
    $html .= "<div><b>Maßnahme:</b> " . $array['measure'] . "</div>";
    $html .= "<hr />";
    $html .= "<h$headerWeight>Nutzen und Schwere der Implementierung</h$headerWeight>";
    $html .= "<div><b>Nutzen:</b> " . ucfirst($mapUsefulness[$array['usefulness']-1]) . "</div>";
    $html .= "<div><b>Benötigtes Wissen:</b> " . ucfirst($mapKnowLedge[$array['easeOfImplementation']['knowledge']-1]) . "</div>";
    $html .= "<div><b>Benötigte Zeit:</b> " . ucfirst($mapTime[$array['easeOfImplementation']['time']-1]) . "</div>";
    $html .= "<div><b>Benötigte Ressourcen (Systeme):</b> " . ucfirst($mapResources[$array['easeOfImplementation']['resources']-1]) . "</div>";
    return $html;
}

function getElementByName($dimensions, $name)
{
    foreach ($dimensions as $dimensionName => $subDimension) {
        foreach ($subDimension as $subDimensionName => $elements) {
            foreach ($elements as $elementName => $element) {
                if ($elementName == $name) {
                    return $element;
                }
            }
        }
    }
}