{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/account.css" rel="stylesheet" />
  <link href="/css/forms.css" rel="stylesheet" />
  <link href="/css/images.css" rel="stylesheet" />
  {if $acc_self}
  <script src="/upload_photo/js/plugin.js"></script>
  <script>
  {literal}
  $(function(){
    $('div.avatar button.upload').getUpload({
         'uploadType'  : 'user_av',
         'item_id'     : '{/literal}{$user_info.users_id}{literal}',
         'width'       : '200',
         'height'      : '240',
         {/literal}
         {if !empty($user_info.users_photo_id)}
         'image_id'    : '{$user_info.users_photo_id}',
         {/if}
         {literal}
         'count'       : '1',
         'sizes'       : 's#200#240'
    });
  });
  {/literal}
  </script>
  {/if}
  <script src="/js/images.js"></script>
{/block}
{block name='div.main'}
  {include file="header.tpl"}
  <section id="my_account" class="block_with_menu">
    <aside>
      <div class="avatar">
         {if !empty($user_info.users_photo_id)}
            <img src="/scripts/uploads/{$user_info.users_photo_id}_s.jpg" />
         {else}
            <img src="/img/avatar.png" />
         {/if}
         {if $acc_self}
            <div class="in">
               <button class="upload" type="submit">{if !empty($user_info.users_photo_id)}Поменять аватар{else}Загрузить аватар{/if}</button>
            </div>
         {/if}
      </div>
      {if $acc_self}
        <div class="control">
          <ul>
            {if $acc_self}
               <li><div class="change edit"><a href="/change_data/?type=change_name">Изменить имя</a></div></li>
               <li><div class="change edit"><a href="/change_data/?type=change_password">Изменить пароль</a></div></li>
            {/if}
            <li><div class="change delete"><a href="/change_data/?type=delete_acc" class="delete" data-id="{$user_info.users_id|default:''}">Удалить аккаунт</a></div></li>
          </ul>
        </div>
      {/if}
      <div class="statistic">
        <ul>
          <li><span>Зарегистрирован:</span>{$user_info.users_register_date|default:''}</li>
          <li><span>Последнее обновление:</span>{$user_info.users_last_update|default:''}</li>
          <li><span>Количество объявлений:</span><span id="works_count">{$user_info.ad_amount|default:0}</span></li>
          <li><span>Просм. профиля:</span>{$user_info.users_profile_views|default:0}</li>
        </ul>
      </div>
    </aside>
    <div class="right_block">
      <h1>{$user_info.users_name|default:''} {$user_info.users_surname|default:''}</h1>
      <ul class="style_menu">
        <li><a href="/profile{if isset($user_id)}/?user={$user_id}{/if}" data="about">Обо мне</a></li><li><a href="/profile/ad{if isset($user_id)}/?user={$user_id}{/if}" data="ad">Мои Объявления</a></li>
      </ul>
      <div id="about">
        <section class="account_block" id="additional">
          <h1>Дополнительная информация</h1>
          {if $acc_self}
            <div class="change edit"><a href="/change_data/?type=change_extra_information">Изменить</a></div>
          {/if}
          <div class="info">
            <p>{$user_info.users_description|default:''}</p>
          </div>
          <div class="contacts ul_info">
            <h2>Контактная информация</h2>
            {if $acc_self}
              <div class="change edit"><a href="/change_data/?type=change_contact_information">Изменить</a></div>
            {/if}
            <ul>
              <li><span>Комната:</span>{$user_info.users_room|default:''}</li>
              <li><span>Телефон:</span>{$user_info.users_phone|default:''}</li>
            </ul>
          </div>
        </section>
      </div>
      <script type="text/javascript">
        $('#my_account .style_menu a[data="{$selected_a|default:''}"]').addClass('active');
      </script>
    </div>
  </section>
{/block}