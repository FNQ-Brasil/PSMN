

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
        $questionValue = $question['QuestionValue'];
        $questionType = $question['QuestionTypeId'];
        $questionTypeCssName = ( Model_QuestionType::AGREEDISAGREE_ID ? 'questionTypeSubmitButton' : 'questionTypeSubmitRadioChange');
      ?>
      <li id="marker-<?php echo $cont; ?>"><a href="#tab-<?php echo $cont; ?>"><span></span><?php echo $cont++; ?></a></li>
      <?php endforeach; ?>
    </ul>
      <div id="tabs-container">
        <div id="tabs-list">
     <?php
      $cont = 1;
      $pesos = array(
        1 => "dis-totalemente",
        2 => "discordo",
        3 => "nao-sei",
        4 => "concordo",
        5 => "con-plenament",
      );
      foreach ($this->blockQuestions as $questionId => $question):
        $questionValue = $question['QuestionValue'];
        $questionType = $question['QuestionTypeId'];
        $questionTypeCssName = ( Model_QuestionType::AGREEDISAGREE_ID ? 'questionTypeSubmitButton' : 'questionTypeSubmitRadioChange');
      ?>

      <div id="tab-<?php echo $cont; ?>" class="tab-item">
        <form action="" class="formsubmitfull" data-question-id="<?php echo $questionId; ?>">
           <div class="label"> <span class="number"><?php echo $cont++; ?>.</span> <?php echo $questionValue; ?></div>
          <div class="answers" style="height: 146px">
            <?php
              $contPesos = 1; 
              foreach ($question['Alternatives'] as $alternativeId => $alternative):
            ?>
              <div class="answer" id="<?php echo $pesos[$contPesos++]; ?>">
                <span class="face"></span>
                <input type="radio" value="<?php echo $alternativeId; ?>" name="question[<?php echo $questionId; ?>]" id="alternativeItem<?php echo $alternativeId; ?>" tabindex="-1" />
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
                
              
                    <?php if ($this->loggedAllowed('index', 'questionnaire:respond')): ?>
                        <button class="large btn-submit btSaveQuestionWithFeedback" type="submit" style="float: right; font-size: 16px;" tabindex="-1" <?php if ($this->periodoRespostas === false): ?>onClick="return false;"<?php endif; ?>><span class="icon" data-icon=""></span> <b>Salvar resposta</b></button>     
                    <?php else: ?>
                        <button class="large btn-submit btSaveQuestionWithFeedback" type="button" style="float: right; font-size: 16px; cursor: default; visibility: hidden" tabindex="-1" <?php if ($this->periodoRespostas === false): ?>onClick="return false;"<?php endif; ?>><span class="icon" data-icon=""></span> <b>Salvar resposta</b></button>  
                    <?php endif; ?>
               
                <label for="FdbkQuestion<?php echo $questionId; ?>" class="complement-label"><?php echo $question['SupportingText']; ?></label>
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
