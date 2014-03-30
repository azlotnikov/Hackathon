{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/account.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
  <link href="/css/images.css" rel="stylesheet" />
  <link href="/css/events.css" rel="stylesheet" />
  <script src="/js/list.js"></script>
  {if $acc_self}
  <script src="/upload_photo/js/plugin.js"></script>
  <script>
  {literal}
  $(function(){
    $('div.avatar button.upload').getUpload({
         'uploadType'  : 'user_av',
         'item_id'     : '{/literal}{$user_info.users_id}{literal}',
         'width'       : '200',
         'height'      : '200',
         {/literal}
         {if !empty($user_info.users_photo_id)}
         'image_id'    : '{$user_info.users_photo_id}',
         {/if}
         {literal}
         'count'       : '1',
         'sizes'       : 's#50#50;b#200#200'
    });
  });
  {/literal}
  </script>
  {/if}
  <script src="/js/images.js"></script>
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <div id="center_block">
    <div id="left_profile_block">
      <div id="main_block">
        {if !empty($user_info.users_photo_id)}
          <img src="/scripts/uploads/{$user_info.users_photo_id}_b.jpg" />
        {else}
          <img src="/img/avatar.jpg" />
        {/if}
        <h1>{$user_info.users_name|default:''} {$user_info.users_surname|default:''}</h1>
        {if !empty($user_info.users_phone)}<h2>{$user_info.users_phone}</h2>{/if}
        <h2 class="room">Комната {$user_info.users_room|default:''}</h2>
        <h2>Просмотров профиля: {$user_info.users_profile_views}</h2>
        <h2>Кол-во объявлений: {$user_info.ad_amount}</h2>
        {if $acc_self}
           <button class="upload" type="submit">{if !empty($user_info.users_photo_id)}Поменять аватар{else}Загрузить аватар{/if}</button>
        {/if}
      </div>
      {if $acc_self}
          <div class="control">
            <ul>
              <li><div class="change edit"><a href="/change_data/?type=change_name">Изменить имя</a></div></li>
              <li><div class="change edit"><a href="/change_data/?type=change_password">Изменить пароль</a></div></li>
              <li><div class="change edit"><a href="/change_data/?type=change_contact_information">Изменить информацию</a></div></li>
              <li><div class="change delete"><a href="/change_data/?type=delete_acc" class="delete" data-id="{$user_info.users_id|default:''}">Удалить аккаунт</a></div></li>
            </ul>
          </div>
      {/if}
    </div>
    <div id="right_profile_block">
      {include file='event_list.tpl'}
    </div>
  </div>
{/block}