<?php

require_once(APPLICATION_PATH_LIBS . '/jpgraph/src/jpgraph.php');
require_once(APPLICATION_PATH_LIBS . '/jpgraph/src/jpgraph_bar.php');
require_once(APPLICATION_PATH_LIBS . '/jpgraph/src/jpgraph_radar.php');

class Manager_ManagementTheme {
    protected $ManagementTheme;

    public function __construct(){
        $this->ManagementTheme = new Model_ManagementTheme();
    }

    public function createScoreByThemeGraphic($questionnaireId, $userId, $filepath,$verificador = false){
        $graphDefinitions = $this->getScoreByThemeGraphicDefinitions($questionnaireId, $userId,$verificador);

        $graph = new RadarGraph(600, 300);

        $graph->SetColor("white");
        $graph->SetShadow();
        $graph->SetCenter(0.52,0.55);
        $graph->HideTickMarks();
        $graph->SetTitles($graphDefinitions['ThemeNames']);
        $graph->yscale->SetAutoMax(100);

        $graph->title->Set("Ciclo ".date('Y'));
        $graph->title->SetFont(FF_FONT1,FS_BOLD);

        $graph->axis->SetFont(FF_FONT1,FS_NORMAL);
        $graph->axis->SetWeight(1);
        $graph->axis->SetColor("darkgray");

        $graph->grid->SetLineStyle("dashed");
        $graph->grid->SetColor("darkgray");
        $graph->grid->SetWeight(1);
        $graph->grid->Show();

        $actual = new RadarPlot($graphDefinitions['ThemeScores']);
        $actual->SetColor('darkorchid4','bisque');
        $actual->SetLineWeight(4);

        $graph->Add($actual);

        return $graph->Stroke($filepath);
    }

    protected function getScoreByThemeGraphicDefinitions($questionnaireId, $userId,$verificador = false){
        $scoreByTheme = $this->ManagementTheme->getScoreByTheme($questionnaireId, $userId,$verificador);

        $themeNames = array();
        $themeScores = array();

        foreach($scoreByTheme as $theme){
            $themeNames[] = utf8_decode($theme->getThemeName());
            $themeScores[] = $theme->getThemeScore();
        }

        return array('ThemeNames' => $themeNames, 'ThemeScores' => $themeScores);
    }

}