<?php

namespace Common\Settings\Validators;

use App\Scout\ElasticSearchEngine;
use App\User;
use Exception;
use Arr;
use Laravel\Scout\Builder;
use Laravel\Scout\EngineManager;
use PDOException;
use Throwable;

class SearchConfigValidator
{
    const KEYS = ['scout_driver'];

    public function fails($settings)
    {
        $engineName = Arr::get($settings, 'scout_driver', config('scout.driver'));
        $manager = app(EngineManager::class);

        if ($engineName === 'mysql') {
            return false;
        }

        try {
            if ($engineName === 'elastic') {
                $manager->extend('elastic', function () {
                    return new ElasticSearchEngine();
                });
            }
            $engine = $manager->engine($engineName);
            $builder = new Builder(new User(), 'foo', null);
            if ( ! $engine->search($builder)) {
                return $this->getDefaultErrorMessage();
            }
        } catch (PDOException $e) {
            return ['search_group' => '<bold>pdo_sqlite</bold> extension needs to be enabled in order to use TNTSearch method.'];
        } catch (Exception $e) {
            return $this->getErrorMessage($e);
        } catch (Throwable $e) {
            return $this->getErrorMessage($e);
        }
    }

    /**
     * @param Exception|Throwable $e
     * @return array
     */
    private function getErrorMessage($e)
    {
        $message = $e->getMessage();
        return ['search_group' => "Could not enable this search method: $message"];
    }

    /**
     * @return array
     */
    private function getDefaultErrorMessage()
    {
        return ['search_group' => 'Could not enable this search method.'];
    }
}
