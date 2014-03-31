{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/index.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <div id="center_block">
     <article class="block">
        <img src="/img/screen1.jpg" />
        <h1>Создай свой личный аккаунт!</h1>
        <p>Создавайте события, общайтесь с людьми, будьте в курсе всего что происходит в твоем кампусе!</p>
     </article>
     <article class="block">
        <img src="/img/screen2.jpg" />
        <h1>Создавай свои события, ты же клевый!</h1>
        <p>Ты можешь покупать, продавать, болтать и веселиться со всеми!</p>
     </article>
     <article class="block">
        <img src="/img/screen3.jpg" />
        <h1>Сотни предложений! Не трать время зря!</h1>
        <p>Не верь слухам, общайся и узнавай информацию только от реальных людей!</p>
     </article>
     <article class="block">
        <img src="/img/screen4.jpg" />
        <h1>PROFIT!</h1>
        <p>Ты счастлив!</p>
     </article>

  </div>
{/block}
