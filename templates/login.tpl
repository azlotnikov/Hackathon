{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/login.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <div id="center_block">
     <h1 class="top_h1">Вход</h1>
     <p class="small">Пожалуйста, войдите в свой аккаунт, или, если у вас его еще нет, то пройдите <a href="/registration">моментальную регистрацию</a>!</p>
     <div id="login_div">
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
         <div class="buttons"><button id="login_button" name="submit" value="submit">Войти</button></div>
       </form>
     </div>
  </div>
{/block}
