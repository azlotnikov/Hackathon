{if !empty($events_list)}
  {foreach from=$events_list item=events}
    <section id="{$events.type_name}" class="events on_board">
      <div class="events_info_data">
        <h1 class="top">{$events.type_alias}</h1>
        {foreach from=$events.events item=event}
          <article>
            <img src="/scripts/uploads/{$event.users_photo_id}_s.jpg" class="avatar" />
            <div class="right_info">
              <div class="header">
                <h1><a href="/profile/?user_id={$event.users_id}">{$event.users_name} {$event.users_surname} ({$event.places_number}):</a></h1>
                <date>{$event.events_creation_date}</date>
              </div>
              <h2>{$event.events_header}</h2>
              {if $events.type_name == 'parties'}<span class="due_date">Дата начала: <date>{$event.events_due_date}</date></span>{/if}
              <p>{$event.events_description}</p>
            </div>
          </article><!--
          <div>
            ID евента - {$event.events_id}
            Заголовок - {$event.events_header}
            Дата создания - {$event.events_creation_date}
            Имя фамилия создателя - {$event.users_name} {$event.users_surname}
            АЙДИ создателя - {$event.users_id}
            Место - {$event.places_number}
          </div>-->
        {/foreach}
        <button class="get_more" data-loaded-amount="{$loaded_amount}" data-user-id="{$user_id|default:null}" data-amount="{$events.events|@count}" data-event="{$events.type_key}" data-id="{$events.type_name}">Показать еще</button>
      </div>
    </section>
  {/foreach}
{/if}