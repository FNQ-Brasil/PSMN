

<h1 class="title tquiz">Autoavaliação do <?php echo $this->blockCurrent->getValue(); // Questionário de  ?></h1>

        <?php if (isset($this->enterpriseRow) and isset($this->isViewAdmin) and $this->isViewAdmin): ?>
            <h4 class="subtitle tquiz">
            <?php echo $this->escape($this->enterpriseRow->getSocialName()); ?>
            (<?php echo $this->escape($this->enterpriseRow->getFantasyName()); ?>)</h4>
        <?php endif; ?>



<p class="description-quizz"><?php echo "Para cada questão <strong>clique na resposta</strong> e depois no <strong>botão 'Salvar Resposta'</strong>."; //$this->blockCurrent->getLongDescription(); ?></p>
    <?php if (!$this->isViewAdmin): ?>
        <h4 class="subtitle" style="padding-top: 0px; padding-bottom: 0px;">
            <a style="color: #999" href="/questionnaire/report">
                Clique aqui caso prefira escrever seu <strong>Relato</strong></a>.
        </h4>
    <?php endif; ?>
<div class="innet-content">
  <div class="quizz">
    <ul>
      <?php
      $cont = 1;
      foreach ($this->blockQuestions as $questionId => $question):
        $questionSummary = $question['QuestionSummary'];
        $questionValue = $question['QuestionValue'];
        $questionType = $question['QuestionTypeId'];
        $questionTypeCssName = ( Model_QuestionType::AGREEDISAGREE_ID ? 'questionTypeSubmitButton' : 'questionTypeSubmitRadioChange');
      ?>
      <li id="marker-<?php echo $cont; ?>"><a href="#tab-<?php echo $cont; ?>"><span></span><?php echo $cont++; ?></a></li>
      <?php endforeach; ?>
    </ul>
      <div id="tabs-container" style="height: auto">
        <div id="tabs-list" style="height: auto">
     <?php
      $cont = 1;
      $pesos = array(
        1 => "dis-totalemente",
        2 => "discordo",
        3 => "nao-sei",
        4 => "concordo",
        5 => "con-plenament",
      );
      $disabled = $this->subscriptionPeriodIsClosed ? 'disabled' : '';
      foreach ($this->blockQuestions as $questionId => $question):
        $questionSummary = $question['QuestionSummary'];
        $questionValue = $question['QuestionValue'];
        $supportingText = $question['SupportingText'];
        $questionType = $question['QuestionTypeId'];
        $questionTypeCssName = ( Model_QuestionType::AGREEDISAGREE_ID ? 'questionTypeSubmitButton' : 'questionTypeSubmitRadioChange');
      ?>

      <div id="tab-<?php echo $cont; ?>" class="tab-item" style="height: auto">
        <form action="" class="formsubmitfull" data-question-id="<?php echo $questionId; ?>">
          <div class="label">
            <span class="number"><?php echo $cont++; ?>.</span>
            <span class="summary"><?php echo $questionSummary; ?></span>
            <a class="details">Saiba mais</a>
          </div>
          <div class="helper-questions">

              <p class="title-helper-questions" style="">Perguntas facilitadoras:</p>
              <?php echo $supportingText; ?>

          </div>

          <div class="details" style="display: none">
            <div class='fb-content'>
              <div class="header">
                <h1><?php echo $questionSummary ?></h1>
                <a class="close" href="#"></a>
              </div>
              <?php echo $questionValue ?>
            </div>
          </div>

          <div class="answers" style="height: 146px">
            <?php
              $contPesos = 1; 
              foreach ($question['Alternatives'] as $alternativeId => $alternative):
            ?>
              <div id="<?php echo $pesos[$contPesos++]; ?>" class="answer">
                <span class="face"></span>
                <input type="radio" value="<?php echo $alternativeId; ?>" name="question[<?php echo $questionId; ?>]" 
                id="alternativeItem<?php echo $alternativeId; ?>" tabindex="-1" <?php echo $disabled ?>/>
                <label class="label-inline" for="alternativeItem<?php echo $alternativeId; ?>">
                  <span class="radio-button"></span>
                  <?php echo $alternative['AlternativeValue']; ?>
                </label>                      
              </div>
            <?php endforeach; ?>
            <div class="fill">
              <div class="status-fill"></div>
            </div>            
          </div>
          <div class="clearfix"></div>
          <?php if ($question['ShowEnterpriseFeedback']):?>
            <div class="complement">
                    <?php if ($this->loggedAllowed('index', 'questionnaire:respond') and !$this->subscriptionPeriodIsClosed): ?>
                        <button class="large btn-submit btSaveQuestionWithFeedback" type="submit" style="float: right; font-size: 16px;" tabindex="-1" <?php if ($this->periodoRespostas === false): ?>onClick="return false;"<?php endif; ?>><b>Salvar resposta</b></button>
                        <a class="large btn-submit help" style="float: right; font-size: 16px;"><b>Ajuda</b></a>
                    <?php else: ?>
                        <button class="large btn-submit btSaveQuestionWithFeedback" type="button" style="float: right; font-size: 16px; cursor: default; visibility: hidden" tabindex="-1" <?php if ($this->periodoRespostas === false): ?>onClick="return false;"<?php endif; ?>><b>Salvar resposta</b></button>
                        <a class="large btn-submit help" style="float: right; font-size: 16px;"><b>Ajuda</b></a>
                    <?php endif; ?>
                <label for="FdbkQuestion<?php echo $questionId; ?>" class="complement-label"></label>
              <textarea name="fdbkQuestion[<?php echo $questionId; ?>]" id="FdbkQuestion<?php echo $questionId; ?>" class="complement-field" tabindex="-1"></textarea>
              <div class="responseretu"></div>              
            </div>
          <?php endif; ?>
        </form>
      </div>
      <?php endforeach; ?>  
      </div>    
      </div>    
  </div>
</div>

<div id='questionnaire-intro'>
    <div class='fb-content'>
        <div class='header'>
            <h1>Questionário de Autoavaliação da Gestão do Prêmio SEBRAE Mulher de Negócios - ciclo 2015</h1>
        </div>

        <p>Esta Autoavaliação da Gestão aborda os temas da gestão empresarial que são considerados na análise das
            candidatas concorrendo ao ciclo 2015 do Prêmio SEBRAE Mulher de Negócios (PSMN).</p>
        <p>Nela são apresentados 16 tópicos da gestão empresarial em um pequeno negócio (MPE). Antes de responder sobre
            um determinado tópico, leia e reflita sobre as afirmações apresentadas e, após isso, escolha a opção de
            resposta que melhor reflita sua situação atual. Para saber ainda mais clique no ícone, que uma
            contextualização referencial será apresentada.</p>
        <p>Responda da forma mais realista possível, pois uma devolutiva apontando os pontos fortes e oportunidades de
            melhoria será gerada a partir de suas respostas. Além disso, suas respostas serão validadas por um avaliador
            do Prêmio, caso sua empresa passe para a etapa de visitas, prevista para aquelas candidatas que se
            destacarem em termos de gestão e do relato enviado. <u>As candidatas visitadas que não evidenciarem o que
            tiverem respondido na autoavaliação da gestão ou que foi descrito no relato serão desclassificadas</u>.</p>
        <p>Para cada um dos tópicos de gestão você deverá informar o grau de satisfação com sua atuação como empresária
            usando a escala abaixo:</p>

        <ol>
            <li><strong>Totalmente Satisfeita</strong> - quando todas as afirmações do tópico forem verdadeiras;</li>
            <li><strong>Muito Satisfeita</strong> - quando a maioria (metade mais uma) das afirmações do tópico for verdadeira;</li>
            <li><strong>Pouco Satisfeita</strong> - quando algumas (metade ou menos) das afirmações do tópico forem verdadeiras;</li>
            <li><strong>Nada Satisfeita</strong> - quando nenhuma das informações do tópico for verdadeira.</li>
        </ol>

        <p>Os tópicos estão agrupados conforme os temas de gestão abaixo, a partir dos quais será gerada um gráfico com
            o percentual de atendimento de cada tema, que fará parte da devolutiva, assim como os pontos fortes e
            oportunidades de melhoria conforme o Modelo de Excelência da Gestão, disseminado pela FNQ.</p>

        <hr/>

        <p class='helping-affirmations'>Exemplo</p>
        <p>Tópico: Sobre a gestão da minha empresa:</p>

        <div class='example'>
            <ul>
                <li>Minha gestão é adequada para meus clientes e fornecedores;</li>
                <li>Nunca preciso alterar a forma como vendo meus produtos;</li>
                <li>Tenho uma equipe de colaboradores motivados;</li>
                <li>Tenho clientes satisfeitos;</li>
                <li>Fico feliz com os resultados da empresa.</li>
            </ul>
        </div>

        <p>( <strong>X</strong> ) Totalmente satisfeita ( ou seja, considero todas afirmações acima como verdadeiras
            para o tópico em questão: Gestão da Empresa)</p>
        <p>( &nbsp;&nbsp;&nbsp; ) Muito Satisfeita</p>
        <p>( &nbsp;&nbsp;&nbsp; ) Pouco Satisfeita</p>
        <p>( &nbsp;&nbsp;&nbsp; ) Nada Satisfeita</p>

        <div class='close'>
            <button class='large btn-submit'><b>Responder Questionário</b></button>
        </div>
    </div>
</div>