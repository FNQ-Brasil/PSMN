<?php

class Report_Devolutive_NextSteps extends Report_Devolutive_CandidateDataPage {


    public function __construct($devolutiveReport,$devolutiveRow){
        parent::__construct($devolutiveReport,$devolutiveRow);

        $this->create();
    }

    protected function create(){
        $this->addPage();
        $this->createText();
    }

    protected function createText(){
        $this->setFontToBold();
        $this->writeFormatted("Próximos Passos do PSMN \n\n");

        $this->setFontToNormal();
        $this->writeFormatted("Após a candidatura ao Prêmio (Preenchimento da Ficha de Inscrição completa, da Autoavaliação sobre o Negócio e do Relato dentro dos Padrões descritos no regulamento), as próximas etapas são:\n\n");
        $this->writeFormatted("1) A candidatura será avaliada pela coordenação do Prêmio de seu Estado considerando sua Autoavaliação sobre sua Gestão e o Relato, que é lido por Avaliadores voluntários; \n\n");
        $this->writeFormatted("2) Se a sua Gestão e seu relato se destacarem no processo de avaliação do Prêmio, você será contatada pelo Gestor, que solicitará o envio da documentação descrita no regulamento, e agendará a visita do(s) Verificador(es). Isso normalmente acontece até final de setembro, publicado no Cronograma (vide Cronograma no portal do Prêmio). O não recebimento do contato do Gestor significa que sua candidatura não avançou no ciclo vigente. Nesse caso, a recomendação é que você tente novamente no próximo ciclo do Prêmio. As informações e soluções do SEBRAE desta devolutiva poderão lhe ser úteis; \n\n");
        $this->writeFormatted("3) As Empresárias classificadas recebem a visita do Verificador, cujo papel é validar as informações da Autoavaliação da Gestão e do Relato;\n\n");
        $this->writeFormatted("4) As Vencedoras são conhecidas na cerimônia da etapa Estadual, ou tornadas públicas por meio de comunicação oficial, e passam a também concorrer na etapa Nacional do Prêmio;\n\n");
        $this->writeFormatted("5) As vencedoras Estaduais que se destacarem na Etapa Nacional receberão nova visita de verificação. \n\n");
        $this->writeFormatted("6) As Vencedoras Estaduais são levadas a Brasília para a cerimônia Nacional, quando as Vencedoras Nacionais são conhecidas;\n\n");
        $this->writeFormatted("7) As Vencedoras Ouro, Prata e Bronze da etapa Nacional recebem um convite para participação de um Evento em Gestão em Território Nacional. A coordenação nacional fará contato quando a data e local tiverem sido definidos; \n\n");
        $this->writeFormatted("8) As Vencedoras Nacionais Ouro participam de uma Missão Internacional. A coordenação nacional fará contato quando a data e destino tiverem sido definidos.");
    }

    protected function setFontToBold(){
        $this->setFont('Helvetica', 'B', 11);
    }

    protected function setFontToNormal(){
        $this->setFont('Helvetica', '', 11);
        $this->setTextColorToBlack();
    }

    protected function writeFormatted($text){
        parent::writeWithUTF8(0.6, $text);
    }
}