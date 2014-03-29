{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/registration.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <h1 class="top_h1">Регистрация</h1>
  <div id="registration">
    <form method="POST" name="reg_form">
      <p>Регистрация проста и занимает всего минуту!</p>
      <span class="error top_error">{$db_error|default:''}</span>
      <div class="form_block">
        <label for="login">Логин*</label>
        <input id="login" type="text" name="login" value="{$login|default:''}"/>
      </div>
      <div class="form_block">
        <label for="name">Имя*</label>
        <input id="name" type="text" name="name" value="{$name|default:''}"/>
      </div>
      <div class="form_block">
        <label for="surname">Фамилия</label>
        <input id="surname" type="text" name="surname" value="{$surname|default:''}"/>
      </div>
      <div class="form_block">
        <label for="room">Комната*</label>
        <input id="room" type="text" name="room" value="{$room|default:''}"/>
      </div>
      <div class="form_block">
        <label for="pass">Пароль*</label>
        <input id="pass" type="password" name="pass" />
      </div>
      <div class="form_block">
        <label for="re_pass">Повторите пароль*</label>
        <input id="re_pass" type="password" name="repass" />
      </div>
      <small>* - обязательные для заполнения поля</small>
      <div class="buttons"><button id="send_button" name="submit" value="submit">Зарегистрироваться</button></div>
    </form>
  </div>
{/block}
