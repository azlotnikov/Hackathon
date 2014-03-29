{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/login.css" rel="stylesheet" />
  <link href="/css/account.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <h1 class="top_h1">Вход</h1>
  <p>Пожалуйста, войдите в свой аккаунт, или, если у вас его еще нет, то пройдите <a href="/registration">моментальную регистрацию</a>!</p>
  <div id="login">
    <form method="POST" name="login_form">
      <span class="error top_error">{$errorMsg|default:''}</span>
      <div class="form_block">
        <label for="login">Логин</label>
        <input id="login" name="login" value="{$login}" /><span class="error">{$errorLogin|default:''}</span>
      </div>
      <div class="form_block">
        <label for="pass">Пароль</label>
        <input id="pass" type="password" name="pass" />
      </div>
      {if $hasCaptcha|default:false}
        <div class="form_block">
          <label for="keystring">Введите текст с картинки:</label>
          <div><img src="{$captcha_img_url}" id="img_kaptcha"></div>
          <input type="text" name="keystring" id="keystring" /><span class="error">{$errorCaptcha|default:''}</span>
        </div>
      {/if}
      <div class="buttons"><button id="login_button" name="submit" value="submit">Войти</button><button id="reg_button" type="button" onClick="javascript:location.assign('/change_data/?type=forgotten_pass');">Забыли пароль?</button></div>
    </form>
  </div>
{/block}
