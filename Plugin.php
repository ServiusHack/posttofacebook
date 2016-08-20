<?php namespace ServiusHack\PostToFacebook;

use Db;
use Log;
use URL;
use Event;
use Backend;
use Flash;
use Cms\Classes\Router;
use Cms\Classes\Theme;
use System\Classes\SettingsManager;
use System\Classes\PluginBase;
use ServiusHack\PostToFacebook\models\Settings;

/**
 * PostToFacebook Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'serviushack.posttofacebook::lang.plugin.name',
            'description' => 'serviushack.posttofacebook::lang.plugin.description',
            'author'      => 'ServiusHack',
            'icon'        => 'icon-facebook'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'ServiusHack\PostToFacebook\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'serviushack.posttofacebook.configure' => [
                'tab' => 'serviushack.posttofacebook::lang.plugin.name',
                'label' => 'serviushack.posttofacebook::lang.permissions.configure'
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'serviushack.posttofacebook::lang.plugin.name',
                'description' => 'serviushack.posttofacebook::lang.plugin.description',
                'icon'        => 'icon-facebook',
                'class'       => 'ServiusHack\PostToFacebook\Models\Settings',
                'category'    => SettingsManager::CATEGORY_SOCIAL
            ]
        ];
    }

    public function boot()
    {
        Event::listen('rainlab.blog.post.created', function($model) {
          if ($model->published)
          {
            $fb_post_id = Plugin::createAtFacebook($model);
            if ($fb_post_id !== null)
            {
              DB::table('posttofacebook_posts')->insert([
                'fb_post_id' => $fb_post_id,
                'blog_post_id' => $model->id
              ]);
            }
          }

        });

        Event::listen('rainlab.blog.post.updated', function($model) {
          $fb_post_id = Db::table('posttofacebook_posts')->where('blog_post_id', $model->id)->pluck('fb_post_id');

          if ($model->published && $fb_post_id === null)
          {
            // Published and not posted. Create it.
            $fb_post_id = Plugin::createAtFacebook($model);
            if ($fb_post_id !== null)
              DB::table('posttofacebook_posts')->insert([
                'fb_post_id' => $fb_post_id,
                'blog_post_id' => $model->id
              ]);
          }
          else if ($model->published && $fb_post_id !== null)
          {
            // Published and posted yet. Update it.
            Plugin::updateAtFacebook($fb_post_id, $model);
          }
          else if (!$model->published && $fb_post_id === null)
          {
            // Not published and not posted yet. Nothing to do.
          }
          else if (!$model->published && $fb_post_id !== null)
          {
            // Not published and posted. Delete it.
            $success = Plugin::deleteAtFacebook($fb_post_id);
            if ($success)
              Db::table('posttofacebook_posts')->where('blog_post_id', $model->id)->delete();
          }

        });

        Event::listen('rainlab.blog.post.deleted', function($model) {
          $fb_post_id = Db::table('posttofacebook_posts')->where('blog_post_id', $model->id)->pluck('fb_post_id');

          if ($fb_post_id !== null)
          {
            Plugin::deleteAtFacebook($fb_post_id);
            Db::table('posttofacebook_posts')->where('blog_post_id', $model->id)->delete();
          }
        });
    }

    static private function getUrl($model)
    {
      $theme = Theme::getActiveTheme();
      $router = new Router($theme);
      $parameters = [
          'id' => $model->id,
          'slug' => $model->slug,
      ];
      $pageName = Settings::get('post_page');
      return URL::to($router->findByFile($pageName, $parameters));
    }

    static private function createAtFacebook($model)
    {
      $url = Plugin::getUrl($model);

      $params = array(
        'link' => $url,
        'access_token' => Settings::get('fb_page_access_token')
      );

      //TODO: Handle values out of range for Facebook.
      if ($model->published_at->isToday())
      {
        $params['published'] = true;
      }
      else if ($model->published_at->isFuture())
      {
        $params['scheduled_publish_time'] = $model->published_at->getTimestamp();
        $params['published'] = false;
      }
      else
      {
        $params['backdated_time'] = $model->published_at->toIso8601String();
      }

      $facebookPageId = Settings::get('fb_page_id');

      $ch = curl_init("https://graph.facebook.com/v2.6/$facebookPageId/feed");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

      $body = curl_exec($ch);
      $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($status_code == 200)
      {
        if ($model->published_at->isToday())
          Flash::success('serviushack.posttofacebook::lang.flash.published');
        else if ($model->published_at->isFuture())
          Flash::success('serviushack.posttofacebook::lang.flash.scheduled');
        else
          Flash::success('serviushack.posttofacebook::lang.flash.backdated');
        $data = json_decode($body);
        return $data->id;
      }
      else
      {
        Flash::error('serviushack.posttofacebook::lang.flash.failed_posting');
        unset($params['access_token']);
        Log::error("Failed posting to Facebook.", array(
          'params' => $params,
          'status code' => $status_code,
          'body' => $body
        ));
        return null;
      }
    }

    static private function updateAtFacebook($post_id, $model)
    {
      $url = Plugin::getUrl($model);

      $params = array(
        'link_edit' => json_encode([
          'link_data' => [
            'link' => $url
          ]
        ]),
        'access_token' => Settings::get('fb_page_access_token')
      );

      //TODO: Handle values out of range for Facebook. see documentation scheduled_publish_time. Also not before page creation time
      //TODO: What does not work: Move published post into the future.
      if ($model->published_at->isToday())
      {
        $params['is_published'] = true;
      }
      else if ($model->published_at->isFuture())
      {
        $params['scheduled_publish_time'] = $model->published_at->getTimestamp();
        $params['published'] = false;
      }
      else
        $params['backdated_time'] = $model->published_at->toIso8601String();

      $ch = curl_init("https://graph.facebook.com/v2.6/$post_id");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

      $body = curl_exec($ch);
      $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($status_code == 200)
      {
        if ($model->published_at->isToday())
          Flash::success('serviushack.posttofacebook::lang.flash.updated');
        else if ($model->published_at->isFuture())
          Flash::success('serviushack.posttofacebook::lang.flash.scheduled_existing');
        else
          Flash::success('serviushack.posttofacebook::lang.flash.backdated_existing');
      }
      else
      {
        Flash::error('serviushack.posttofacebook::lang.flash.failed_updating');
        unset($params['access_token']);
        Log::error("Failed updating post at Facebook.", array(
          'params' => $params,
          'status code' => $status_code,
          'body' => $body
        ));
      }
      curl_close($ch);
    }

    static private function deleteAtFacebook($post_id)
    {
      $params = array(
        'access_token' => Settings::get('fb_page_access_token')
      );

      $ch = curl_init("https://graph.facebook.com/v2.6/$post_id");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
      curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

      $body = curl_exec($ch);
      $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if ($status_code == 200)
      {
          Flash::success('serviushack.posttofacebook::lang.flash.deleted');
          return true;
      }
      else
      {
        Flash::error('serviushack.posttofacebook::lang.flash.failed_deleting');
        Log::error("Failed deleting post at Facebook.", array(
          'status code' => $status_code,
          'body' => $body
        ));
        return false;
      }
      curl_close($ch);
    }

}
