{extends file='account.base.tpl'}
{block name='account_body'}
{assign var="selected_a" value="about"}
<div id="about">
  <section class="account_block" id="my_categories">
    <h1>Специализации</h1>
    {if $acc_self}
      <small>В зависимости от того, в какие категории вы загружаете в фото или видеосессии, определяются ваши специализации.</small>
    {/if}
    <ul>
      {if $user_spec.ps|@count > 0}
      <li><b>Фотография</b>
        <ul>
        {foreach from=$ps_cats item=cat_name key=cat_id}
          {if isset($user_spec.ps[$cat_id])}<li>{$cat_name}</li>{/if}
        {/foreach}
        </ul>
      </li>
      {/if}
      {if $user_spec.vs|@count > 0}
      <li><b>Видеография</b>
        <ul>
        {foreach from=$vs_cats item=cat_name key=cat_id}
          {if isset($user_spec.vs[$cat_id])}<li>{$cat_name}</li>{/if}
        {/foreach}
        </ul>
      </li>
      {/if}
    </ul>
  </section>
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
        <li><span>E-mail:</span><a href="mailto:{$user_info.users_email|default:''}">{$user_info.users_email|default:''}</a></li>
        <li><span>Skype:</span><a href="skype:{$user_info.users_skype|default:''}">{$user_info.users_skype|default:''}</a></li>
        <li><span>Веб-сайт:</span><a href="{$user_info.users_site|default:''}">{$user_info.users_site|default:''}</a></li>
        <li><span>Телефон:</span>{$user_info.users_phone|default:''}</li>
      </ul>
    </div>
  </section>
</div>
{/block}