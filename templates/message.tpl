{extends file='page.tpl'}
{block name='links' append}
  <link href="/css/header.css" rel="stylesheet" />
  <link href="/css/footer.css" rel="stylesheet" />
  <link href="/css/message.css" rel="stylesheet" />
  {if $isGoAcc|default:false}
      <meta http-equiv="Refresh" content="3; URL=/profile">
  {else}
      <meta http-equiv="Refresh" content="3; URL=/">
  {/if}
{/block}
{block name='div.main'}
   {include file="header.tpl"}
    <div id="message">
    {$errorMsg|default:''}
    {$successMsg|default:''}
    {if $data|default:false}
      Данные успешно изменены!
    {elseif $extra_data|default:false}
      Дополнительная информация успешно изменена!
    {elseif $contact_data|default:false}
      Контактная информация успешно изменена!
    {elseif $new_pass|default:false}
      На указанный вами email отправлено письмо с новым паролем.
    {elseif $isChangePass|default:false}
      Пароль успешно изменен!
    {elseif $name_data|default:false}
      Имя и фамилия успешно изменены!
    {/if}
    {if $isGoAcc|default:false}<br /><br />Через несколько секунд вы автоматически перейдете на страницу своего аккаунта. Если этого не произошло, вы можете пройти по <a href="/profile">ссылке</a> в свой аккаунт.
    {else}
      <br /><br />Через несколько секунд вы автоматически перейдете на главную страницу. Если этого не произошло, вы можете пройти по <a href="/">ссылке</a> на главную страницу.
    {/if}
   </div>
{/block}
