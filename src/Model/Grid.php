<?php
/**
 * Этот файл является частью расширения модуля веб-приложения RosGear.
 * 
 * @link https://rosgear.ru/
 * @copyright Copyright (c) 2015 RosGear
 * @license https://rosgear.ru/license/
 */

namespace Rg\Backend\Config\Services\Model;

use Ge;
use Ge\Panel\Data\Model\ArrayGridModel;

/**
 * Модель списка служб.
 * 
 * @author Anton Tivonenko <anton.tivonenko@gmail.com>
 * @package Rg\Backend\Config\Services\Model
 * @since 1.0
 */
class Grid extends ArrayGridModel
{
    /**
     * Имена служб в загрузке конфигурации приложения.
     * 
     * @var array
     */
    protected array $appBootstrap = [];

    /**
     * Имена служб в загрузке унифицированной конфигурации приложения.
     * 
     * @var array
     */
    protected array $uniBootstrap = [];

    /**
     * {@inheritdoc}
     */
    public function getDataManagerConfig(): array
    {
        return [
            'fields' => [
                ['id'],
                ['name'],
                ['icon'],
                ['className'],
                ['bootstrapBackend'],
                ['bootstrapFrontend'],
                ['ownerConfig']
            ],
            'filter' => [
                'name'      => ['operator' => 'like'],
                'className' => ['operator' => 'like'],
                'ownerConfig' => ['operator' => '=='],
            ]
        ];
    }

    /**
     * {@inheritdoc}
     * 
     * @return \Ge\Config\Config
     */
    public function getRowsBuilder()
    {
        return Ge::$app->services->config;
    }

    /**
     * {@inheritdoc}
     * 
     * @return array
     */
    public function buildQuery($builder)
    {
        return $builder->getAll();
    }

    /**
     * {@inheritdoc}
     */
    public function beforeFetchRows(): void
    {
        $this->appBootstrap = Ge::$app->config->bootstrap;
        if (isset(Ge::$app->unifiedConfig->bootstrap)) {
            $this->uniBootstrap = Ge::$app->unifiedConfig->bootstrap;
        } else
            $this->uniBootstrap = [];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeFetchRow(mixed $row, int|string $rowKey): ?array
    {
        if (is_string($row)) {
            $className  = $row;
            $ownerConfig = 0;
        } else {
            $className  = $row['class'] ?? '';
            $ownerConfig = (int) isset($row['config']);
        }
        // т.к. параметр загрузки в конфигурации приложения приоритетней, то скрываем 
        // их, т.к. нет смысла их менять, останутся прежними
        if (isset($this->appBootstrap[$rowKey])) {
            $bootstrapBackend  = -1;
            $bootstrapFrontend = -1;
        } else
        // если имя службы  указано в параметре загрузки унифицированного конфигуратора приложения
        if (isset($this->uniBootstrap[$rowKey])) {
            $bootstrapBackend  = $this->uniBootstrap[$rowKey][BACKEND] ?? false;
            $bootstrapFrontend = $this->uniBootstrap[$rowKey][FRONTEND] ?? false;
        } else {
            $bootstrapBackend  = false;
            $bootstrapFrontend = false;
        }
        return [
            'id'                => $rowKey,
            'name'              => $rowKey,
            'className'         => $className,
            'bootstrapBackend'  => (int) $bootstrapBackend,
            'bootstrapFrontend' => (int) $bootstrapFrontend,
            'ownerConfig'       => $ownerConfig
        ];
        return $row;
    }
}
