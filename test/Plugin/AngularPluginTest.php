<?php
/**
 * @copyright 2017 Hostnet B.V.
 */
declare(strict_types=1);
namespace Hostnet\Component\Resolver\Plugin;

use Hostnet\Component\Resolver\Bundler\Pipeline\MutableContentPipelineInterface;
use Hostnet\Component\Resolver\Cache\CachedImportCollector;
use Hostnet\Component\Resolver\Config\ConfigInterface;
use Hostnet\Component\Resolver\Event\AssetEvents;
use Hostnet\Component\Resolver\Import\BuiltIn\AngularImportCollector;
use Hostnet\Component\Resolver\Import\MutableImportFinderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @covers \Hostnet\Component\Resolver\Plugin\AngularPlugin
 */
class AngularPluginTest extends TestCase
{
    /**
     * @dataProvider activateProvider
     */
    public function testActivate(string $expected_collector_class, bool $is_dev)
    {
        $event_dispatcher = new EventDispatcher();

        $pipeline = $this->prophesize(MutableContentPipelineInterface::class);
        $cache    = $this->prophesize(CacheInterface::class);
        $config   = $this->prophesize(ConfigInterface::class);
        $config->isDev()->willReturn($is_dev);
        $config->getEventDispatcher()->willReturn($event_dispatcher);
        $finder = $this->prophesize(MutableImportFinderInterface::class);
        $finder->addCollector(Argument::type($expected_collector_class))->shouldBeCalled();
        $plugin_api     = new PluginApi($pipeline->reveal(), $finder->reveal(), $config->reveal(), $cache->reveal());
        $angular_plugin = new AngularPlugin();
        $angular_plugin->activate($plugin_api);
        self::assertCount(1, $event_dispatcher->getListeners(AssetEvents::POST_PROCESS));
    }

    public function activateProvider(): array
    {
        return [
            [AngularImportCollector::class, false],
            [CachedImportCollector::class, true]
        ];
    }
}
