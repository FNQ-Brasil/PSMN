<?php
  $home = array(
    'mpe-brasil' => array(
      'title'       => 'MPE Brasil',
      'subtitle'    => 'Prêmio de Competitividade para Micro e pequenas Empresas 2013',
      'label'       => 'Infome o CNPJ da sua empresa ou CPF caso produtor rural',
      'placeholder' => 'Digite seu documento ou usuário caso o tenha',
      'button'      => 'Entrar',
      'title2'      => 'Prepare sua empresa para esse excelente momento do Brasil',
      'text1'       => 'Que brasileiro é criativo, todo mundo sabe. É por isso que muitas empresas já estão se preparando para a Copa do Mundo e as Olimpíadas no Brasil. Para sua micro ou pequena empresa também aproveitar este excelente momento, participe do Prêmio MPE Brasil.',
      'text2'       => 'Ao se inscrever, você ganha uma análise de gestão personalizada e, com essa força, o ouro vai chegar mais rápido do que você imagina.',
      'text3'       => ''
      ),
    'mpe-diagnostico' => array(
      'title'       => 'MPE  Diagnóstico',
      'subtitle'    => 'Ciclo 2015',
      'label'       => 'Digite seu CPF, CNPJ ou usuário',
      'placeholder' => 'Digite seu documento ou usuário caso o tenha',
      'button'      => 'Entrar',
      'title2'      => '',
      'text1'       => '',
      'text2'       => '',
      'text3'       => ''
      ),
    'sebrae-mais' => array(
      'title'       => 'Sebrae Mais',
      'subtitle'    => 'Programa SEBRAE para empresas avançadas',
      'label'       => 'Digite seu CPF, CNPJ ou usuário',
      'placeholder' => 'Digite seu documento ou usuário caso o tenha',
      'button'      => 'Entrar',
      'title2'      => 'Autodiagnóstico da gestão de sua empresa',
      'text1'       => 'Esse diagnóstico é indicado para empresas que buscam um panorama mais completo dos processos de gestão, pois as permite conhecer, ao final do autodiagnóstico, seus pontos fortes e oportunidades de melhorias, além de apontar caminhos para a melhoria contínua.',
      'text2'       => 'Para uma melhor compreensão dos Fundamentos e Critérios de Excelência, a FNQ sugere que seja feito o curso virtual do Modelo de excelência da Gestão (MEG®), disponível no Portal www.fnq.org.br.',
      'text3'       => 'A partir do resultado do seu autodiagnóstico, do número de colaboradores e do tempo de existência do seu negócio, o SEBRAE selecionará as empresas para participarem da solução Ferramentas de Gestão Avançada - FGA, integrante do Programa SEBRAE Mais.'
      )
    );

  function t( $text ){
    global $home;
    global $type;
    echo $home[$type][$text];
  }
?>
<main>
  <div id="main">
    <div id="home-login">
      <hgroup>
        <h1><?php t('title'); ?></h1>
        <h2><?php t('subtitle'); ?></h2>
      </hgroup>
      <form method="post" action="/login" id="form-home-login">
        <fieldset>
          <legend>Login</legend>
          <label>
            <span><?php t('label'); ?></span>
            <input type="text" class="required" id="cpf-cnpj" placeholder="<?php t('placeholder'); ?>" required autofocus>
          </label>
          <button type="submit" id="login-submit"><?php t('button'); ?></button>
        </fieldset>
      </form>
    </div>
    <div id="home-text">
      <h3><?php t('title2'); ?></h3>
      <p><?php t('text1'); ?></p>
      <p><?php t('text2'); ?></p>
      <p><?php t('text3'); ?></p>
    </div>
  </div>
</main>

<footer>
    <div id="footer">
      <p>© Copyright Vorttex 2012. Todos os direitos reservados.</p>
    </div>
</footer>
