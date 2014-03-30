{if !empty($events_list)}
  {foreach from=$events_list item=events}
    <div id="{$events.type_name}">
      <p>{$events.type_alias}</p>
      {foreach from=$events.events item=event}
        <div>
          ID евента - {$event.events_id}
          Заголовок - {$event.events_header}
          Дата создания - {$event.events_creation_date}
          Имя фамилия создателя - {$event.users_name} {$event.users_surname}
          АЙДИ создателя - {$event.users_id}
          Место - {$event.places_number}
        </div>
      {/foreach}
    </div>
    <button class="getMore" data-user-id="{$user_id|default:null}" data-amount="{$events.events|@count}" data-event="{$events.type_key}" data-id="{$events.type_name}"></button>
  {/foreach}
{/if}