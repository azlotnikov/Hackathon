{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/change_account.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <div id="change_info">
    {if $type==change_extra_information}
      <form action="/change_data/?type=change_extra_information" method="POST" id="additional">
        <input id="login" name="login" value="{$login}" type="hidden" />
        <div class="form_block">
          <label for="additional"><h1>Дополнительная информация</h1></label>
          <small>Введите сюда дополнительную информацию о себе для тех, кто посетит ваш профиль</small>
          <span class="error top_error">{$errorMsg|default:''}</span><textarea id="additional" name="additional" rows="20" cols="80">{$udata.users_description}</textarea>
        </div>
        <div class="buttons"><button id="save" name="submit" value="submit">Сохранить</button><button id="cancel" type="button" onClick="javascript:location.assign('/profile')">Отмена</button></div>
      </form>
    {elseif $type==change_contact_information}
      <form action="/change_data/?type=change_contact_information" method="POST" id="contacts">
        <h1>Контактные данные</h1>
        <span class="error top_error">{$errorMsg|default:''}</span>
        <input id="login" name="login" value="{$login}" type="hidden" />
        <div class="form_block">
          <label for="room">Комната*</label>
          <input id="room" name="room" value="{$udata.users_room}"/>
        </div>
        <div class="form_block">
          <label for="phone">Телефон</label>
          <input id="phone" type="phone" name="phone" value="{$udata.users_phone}"/>
        </div>
        <div class="buttons"><button id="save" name="submit" value="submit">Сохранить</button><button id="cancel" type="button" onClick="javascript:location.assign('/profile')">Отмена</button></div>
      </form>
    {elseif $type==change_password}
      <form action="/change_data/?type=change_password" method="POST" id="change_pass">
        <h1>Смена пароля</h1>
        <span class="error top_error">{$errorMsg|default:''}</span>
        <div class="form_block">
          <label for="pass">Текущий пароль</label>
          <input id="pass" type="password" name="pass" />
        </div>
        <div class="form_block">
          <label for="new_pass">Новый пароль</label>
          <input id="new_pass" type="password" name="new_pass" />
        </div>
        <div class="form_block">
          <label for="re_new_pass">Повторите новый пароль</label>
          <input id="re_new_pass" type="password" name="re_new_pass" />
        </div>
        <div class="buttons"><button id="save" name="submit" value="submit">Сохранить</button><button id="cancel" type="button" onClick="javascript:location.assign('/profile')">Отмена</button></div>
      </form>
    {elseif $type==delete_acc}
      <form action="/change_data/?type=delete_acc" method="POST" id="delete_acc">
        <h1>Удаление аккаунта</h1>
        <span class="error top_error">{$errorMsg|default:''}</span>
        <small>Вы уверены?</small>
        <div class="buttons"><button id="save" name="submit" value="delete">Да, удалить аккаунт</button><button id="cancel" type="button" onClick="javascript:location.assign('/profile')">Отмена</button></div>
      </form>
    {elseif $type==change_name}
      <form action="/change_data/?type=change_name" method="POST" id="change_name">
      <span class="error">{$errorMsg|default:''}</span>
      <h1>Смена имени</h1>
      <input id="login" name="login" value="{$login}" type="hidden" />
      <div class="form_block">
        <label for="name">Имя*</label>
        <input id="name" name="name" value="{$udata.users_name}"/><span class="error">{$errorName|default:''}</span>
      </div>
      <div class="form_block">
        <label for="surname">Фамилия</label>
        <input id="surname" name="surname" value="{$udata.users_surname}"/>
      </div>
      <div class="buttons"><button id="save" name="submit" value="submit">Сохранить</button><button id="cancel" type="button" onClick="javascript:location.assign('/profile')">Отмена</button></div>
    </form>
    {/if}
  </div>
{/block}