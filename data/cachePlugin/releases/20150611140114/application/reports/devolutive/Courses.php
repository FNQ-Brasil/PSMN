<?php

class Report_Devolutive_Courses extends Report_Devolutive_CandidateDataPage {

    protected $HREF = ' ';

    public function __construct($devolutiveReport,$devolutiveRow){
        parent::__construct($devolutiveReport,$devolutiveRow);

        $this->create();
    }

    protected function create(){
        $this->addPage();
        $this->createDataTable();

    }

    protected function createDataTable(){
        $headers = array('Cód.Curso','Modalidade','Nome do Curso');

        $this->heading('Portfólio de Cursos do Sebrae');
        $this->ln();
        $this->ln();

        $this->createIntroduction();
        $this->ln();
        $this->ln();

        $themes = $this->getScoreByTheme();
        for($i = 0; $i < count($themes); $i++){

            if($themes[$i]->getThemeScore() < 80.0) {
                $this->setThemeTitle($themes[$i]->getThemeName());
                $courseRows = $this->getCoursesData($themes[$i]->getThemeId());
                $this->dataTable($headers, $courseRows, 1, 1, array('C','C','L'), array(2,2.2,14.8));

                if($i !== count($themes)-1) {
                    $this->ln();
                }
            }
        }
    }

    protected function createIntroduction(){
        $this->setFontToBold();

        $this->writeFormatted("Soluções de Cursos do Sebrae \n\n");

        $this->setFontToNormal();

        $this->writeFormatted("Atualmente o mercado está bastante concorrido. Para que empresárias como vocês possam se destacar é necessário buscar atualização, aperfeiçoamento e conhecimento constante. Por isso o Sebrae oferece excelentes serviços que podem auxiliá-la no atendimento das");
        $this->writeFormatted(" necessidades do seu negócio. No site ");

        $this->writeLink("<A HREF='www.sebrae.com.br'>www.sebrae.com.br</A>");

        $this->setFontToNormal();

        $this->writeFormatted(", você encontra soluções desenvolvidas pelo Sebrae e também soluções criadas por parceiros inovadores do mercado. \n\n");

        $this->writeFormatted("Para facilitar sua busca, trouxemos para essa devolutiva as principais soluções de cursos EAD do Sebrae, que estão disponíveis na internet, através do endereço: ");

        $this->writeLink("<A HREF='http://www.ead.sebrae.com.br'>http://www.ead.sebrae.com.br.</A>");

        $this->setFontToNormal();

        $this->writeFormatted("\n\nAproveite as ferramentas de gestão abaixo e impulsione o seu negócio.");
    }

    protected function getScoreByTheme(){
        $modelManagementTheme = new Model_ManagementTheme();
        return $modelManagementTheme->getScoreByTheme($this->getQuestionnaireId(),$this->getUserId());
    }

    protected function getQuestionnaireId(){
        return $this->devolutiveRow->getQuestionnaireId();
    }

    protected function getUserId(){
        return $this->devolutiveRow->getUserId();
    }

    protected function setThemeTitle($themeName){
        $this->subtitle($themeName);
        $this->ln();
    }

    protected function getCoursesData($themeId){
        $rows = array();
        foreach($this->getCoursesOfTheme($themeId) as $course){
            $courseType = $this->getCourseType($course->getCourseTypeId());
            $rows[] = array($course->getCode(),$courseType,$course->getName());
        }

        return $rows;
    }

    protected function getCoursesOfTheme($themeId){
        $modelCourse = new Model_Course();
        return $modelCourse->getAll(array('ManagementThemeId = ?'=>$themeId));
    }

    protected function getCourseType($courseTypeId){
        $modelCourseType = new Model_CourseType();
        return $modelCourseType->getCourseTypeById($courseTypeId)->getName();
    }

    protected function setFontToDataTableHeader(){
        $this->setFont('Helvetica', 'B', 10);
        $this->setTextColorToOrange();
        $this->setFillColor(255, 255, 255);
    }

    protected function setFontToDataTableBody(){
        $this->setFont('Helvetica', '', 10);
        $this->setTextColorToBlack();
        $this->setFillColor(255, 255, 255);

    }

    protected function setFontToNormal(){
        $this->setFont('Helvetica', '', 11);
        $this->setTextColorToBlack();
    }

    protected function writeFormatted($text){
        parent::writeWithUTF8(0.6, $text);
    }

    protected function setFontToBold(){
        $this->setFont('Helvetica', 'B', 11);
    }

    function writeLink($html) {
        $html = str_replace("\n",' ',$html);
        $link = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);

        $attr = array();

        $attr['HREF'] = $link[2];

        $this->SetHref($attr);

        if ($this->HREF)
            $this->PutLink($this->HREF, $attr['HREF']);

        $this->ClearHref();

    }

    function SetHref($attr) {
        $href = isset($attr['HREF']) ? $attr['HREF'] : 'teste';
        $this->HREF = $href;
    }


    function ClearHref() {
        $this->HREF='';
    }


    function PutLink($URL,$txt) {
        $this->SetTextColor(0,0,255);
        $this->setFont('Helvetica', 'U', 11);
        $this->writeFormattedWithLink($txt,$URL);
        $this->SetTextColor(0);
    }

    protected function writeFormattedWithLink($text,$link){
        parent::writeWithUTF8WithLink(0.6, $text,$link);
    }

}