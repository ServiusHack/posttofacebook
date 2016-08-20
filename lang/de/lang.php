<?php

return [
  'plugin' => [
    'name' => 'Post To Facebook',
    'description' => 'Automatisch Blog-Posts bei Facebook posten'
  ],
  'permissions' => [
    'configure' => 'Konfigurieren'
  ],
  'flash' => [
    'published' => 'Post bei Facebook veröffentlicht',
    'scheduled' => 'Post bei Facebook geplant',
    'backdated' => 'Rückdatiertes Post bei Facebook veröffentlicht',
    'failed_posting' => 'Veröffentlichen bei Facebook ist fehlgeschlagen',
    'updated' => 'Post bei Facebook aktualisiert',
    'scheduled_existing' => 'Vorhandenes Post bei Facebook geplant',
    'backdated_existing' => 'Vorhandenes Post bei Facebook rückdatiert',
    'failed_updating' => 'Aktualisieren des Posts bei Facebook ist fehlgeschlagen',
    'deleted' => 'Post bei Facebook gelöscht',
    'failed_deleting' => 'Löschen des Posts bei Facebook ist fehlgeschlagen'
  ],
  'settings' => [
    'page_id' => 'Facebook-Seiten-ID',
    'page_id_comment' => "Diese ID befindest sich unter 'Info' auf der Facebook-Seite",
    'page_access_token' => 'Page Access Token',
    'page_access_token_comment' => 'Die Dokumentation zum Plugin beschreibt, wie sich das Access Token erzeugen lässt.',
    'post_page' => 'CMS Seite für Blogposts',
    'post_page_comment' => 'Auf diese Seite verweist das Facebook Post',
  ]
];
