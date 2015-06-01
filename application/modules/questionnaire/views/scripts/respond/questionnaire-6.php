
<form>
  <input type="hidden" name="block" value="<?php //echo $blockId; ?>" class="blockId" />
  <fieldset>
    <legend><?php //echo $blockValue; ?></legend>
      <h1 class="title">Questionário de <?php echo $this->blockCurrent->getValue(); ?></h1>
      <h4 class="subtitle">&nbsp;
         <?php if (isset($this->enterpriseRow) and isset($this->isViewAdmin) and $this->isViewAdmin): ?>
            <?php echo $this->escape($this->enterpriseRow->getSocialName()); ?>
            (<?php echo $this->escape($this->enterpriseRow->getFantasyName()); ?>)
        <?php endif; ?>
      </h4>  
      <p class="description-quizz"><?php echo "Para as próximas 30 afirmações escolha entre <strong>'Dificilmente'</strong>, <strong>'Às vezes'</strong> e <strong>'Sempre acontece'</strong>."; //$this->blockCurrent->getLongDescription(); ?></p>
      <div class="progress">
        <div class="status">
          <div class="tooltip-percent" id="percent-tooltip">0<span>%</span></div>
        </div>
      </div>
      <div class="inner-content">
         <ol>
            <?php
              $tabindex = 1;
              foreach ($this->blockQuestions as $questionId => $question):
                $questionTypeCssName = ( Model_QuestionType::AGREEDISAGREE_ID ? 'questionTypeSubmitButton' : 'questionTypeSubmitRadioChange');
            ?>
            <li class="questionItem questionTypeSubmitRadioChange <?php echo $questionTypeCssName; ?>" id="questionItem<?php echo $questionId; ?>" data-question-id="<?php echo $questionId; ?>">
              <div class="texto">
                <?php echo $question['QuestionValue']; ?>
              </div>
              <?php foreach ($question['Alternatives'] as $alternativeId => $alternative): ?>
              <div class="answer-<?php echo $alternativeId; ?>">
                <input id="alternativeItem<?php echo $alternativeId; ?>" type="radio" name="question[<?php echo $questionId; ?>]" value="<?php echo $alternativeId; ?>" />
                <label class="label-inline tab-nav" for="alternativeItem<?php echo $alternativeId; ?>">
                  <span class="radio-button"></span><br>
                  <?php echo $alternative['AlternativeValue']; ?> 
                </label>
              </div>
              <?php endforeach; ?>

              <?php if ($question['ShowEnterpriseFeedback']):?>
                <label for="FdbkQuestion<?php echo $questionId; ?>" ><?php echo $question['SupportingText']; ?></label>
                <textarea id="FdbkQuestion<?php echo $questionId; ?>" name="fdbkQuestion[<?php echo $questionId; ?>]"></textarea>
                <input type="button" value="Salvar" class="btSaveQuestionWithFeedback" disabled />
              <?php endif; ?>

            </li>
          <?php endforeach; ?>
          </ol>
      </div>



  </fieldset>
</form>