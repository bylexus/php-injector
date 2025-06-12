<?php

namespace {

    use PhpInjector\AutoWire;
    use PHPUnit\Framework\TestCase;
    use Test\Fixture\AutoWire\ComplexConstructor;
    use Test\Fixture\AutoWire\DependsOnExistingClass;
    use Test\Fixture\AutoWire\MixedConstructor;
    use Test\Fixture\AutoWire\NoConstructor;
    use Test\Fixture\AutoWire\NullableTypeConstructor;
    use Test\Fixture\AutoWire\ServiceA;
    use Test\Fixture\AutoWire\ServiceB;
    use Test\Fixture\AutoWire\ServiceC;
    use Test\Fixture\AutoWire\ServiceContainer;
    use Test\Fixture\AutoWire\ServiceD;
    use Test\Fixture\AutoWire\TestParamConstructor;

    class AutoWireTest extends TestCase {

        public function testClassWithoutConstructor() {
            $aw = new AutoWire();
            $inst = $aw->createInstance(NoConstructor::class);
            $this->assertInstanceOf(NoConstructor::class, $inst);
            $this->assertSame('test', $inst->param);
        }

        public function testClassWithoutConstructorFromContainer() {
            $container = new ServiceContainer();
            $dependency = new NoConstructor();
            $dependency->param = 'from_di';
            $container->services[NoConstructor::class] = $dependency;

            $aw = new AutoWire($container);
            $inst = $aw->createInstance(MixedConstructor::class);
            $this->assertInstanceOf(MixedConstructor::class, $inst);
            $this->assertSame($dependency, $inst->dependency);
            $this->assertSame('from_di', $inst->dependency->param);
        }

        public function testClassWithNestedDependenciesFromContainer() {
            $container = new ServiceContainer();
            $a = new ServiceA();
            $b = new ServiceB($a);
            $container->services[ServiceB::class] = $b;
            $container->services[ServiceA::class] = $a;

            $aw = new AutoWire($container);
            $c = $aw->createInstance(ServiceC::class);
            $d = $aw->createInstance(ServiceD::class);

            $this->assertInstanceOf(ServiceC::class, $c);
            $this->assertInstanceOf(ServiceD::class, $d);

            $this->assertSame($b, $c->b);
            $this->assertSame($a, $d->a);
            $this->assertSame($b, $d->b);
            $this->assertNotSame($c, $d->c);
        }

        public function testClassWithExistingClassDependency() {
            $aw = new AutoWire();
            $inst = $aw->createInstance(DependsOnExistingClass::class);
            $this->assertInstanceOf(DependsOnExistingClass::class, $inst);
            $this->assertInstanceOf(NoConstructor::class, $inst->dependency1);
            $this->assertInstanceOf(NoConstructor::class, $inst->dependency2);
            $this->assertNotSame($inst->dependency1, $inst->dependency2);
            $this->assertSame('test', $inst->param);
        }

        public function testClassWithParamConstructor() {
            $aw = new AutoWire();
            $inst = $aw->createInstance(TestParamConstructor::class, ['param' => 'test']);
            $this->assertInstanceOf(TestParamConstructor::class, $inst);
            $this->assertSame('test', $inst->param);
        }

        public function testClassWithMixedConstructor() {
            $aw = new AutoWire();
            $noConstrInst = new NoConstructor();
            $inst = $aw->createInstance(MixedConstructor::class, [
                'param' => 'test',
                $noConstrInst,
            ]);
            $this->assertInstanceOf(MixedConstructor::class, $inst);
            $this->assertSame('test', $inst->param);
            $this->assertInstanceOf(NoConstructor::class, $inst->dependency);
            $this->assertSame($noConstrInst, $inst->dependency);
        }

        public function testClassWithComplexConstructor() {
            $aw = new AutoWire();
            $noConstrInst = new NoConstructor();
            $inst = $aw->createInstance(ComplexConstructor::class, [
                'param' => 'test',
                $noConstrInst,
            ]);
            $this->assertInstanceOf(ComplexConstructor::class, $inst);
            $this->assertSame('test', $inst->param);
            $this->assertInstanceOf(NoConstructor::class, $inst->dependency1);
            $this->assertSame($noConstrInst, $inst->dependency1);
            $this->assertInstanceOf(DependsOnExistingClass::class, $inst->dependency2);
            $this->assertInstanceOf(NoConstructor::class, $inst->dependency2->dependency1);
            $this->assertInstanceOf(NoConstructor::class, $inst->dependency2->dependency2);
        }

        public function testClassWithNullableType() {
            $aw = new AutoWire();

            // With service instance on a nullable parameter:
            $serviceA = new ServiceA();
            $inst = $aw->createInstance(NullableTypeConstructor::class, [
                $serviceA, 'b' => 44
            ]);
            $this->assertInstanceOf(NullableTypeConstructor::class, $inst);
            $this->assertSame($serviceA, $inst->a);
            $this->assertSame(44, $inst->b);

            // With no parameter on a nullable parameter:
            $inst = $aw->createInstance(NullableTypeConstructor::class, []);
            $this->assertInstanceOf(NullableTypeConstructor::class, $inst);
            $this->assertNull($inst->a);
            $this->assertSame(42, $inst->b);

            // With null parameter on a nullable parameter:
            $inst = $aw->createInstance(NullableTypeConstructor::class, ['a' => null]);
            $this->assertInstanceOf(NullableTypeConstructor::class, $inst);
            $this->assertNull($inst->a);
            $this->assertSame(42, $inst->b);
        }
    }
}

namespace Test\Fixture\AutoWire {
    class NoConstructor {
        public string $param = "test";
    }
    class TestParamConstructor {
        public string $param;
        public function __construct(string $param = 'foo') {
            $this->param = $param;
        }
    }

    class MixedConstructor {
        public string $param;
        public NoConstructor $dependency;

        public function __construct(NoConstructor $dependency, string $param = 'foo') {
            $this->param = $param;
            $this->dependency = $dependency;
        }
    }

    class ComplexConstructor {
        public string $param;
        public NoConstructor $dependency1;
        public DependsOnExistingClass $dependency2;

        public function __construct(NoConstructor $dependency1, DependsOnExistingClass $dependency2, string $param = 'foo') {
            $this->param = $param;
            $this->dependency1 = $dependency1;
            $this->dependency2 = $dependency2;
        }
    }

    class DependsOnExistingClass {
        public string $param;
        public NoConstructor $dependency1;
        public NoConstructor $dependency2;

        public function __construct(NoConstructor $dependency1, NoConstructor $dependency2) {
            $this->dependency1 = $dependency1;
            $this->dependency2 = $dependency2;
            $this->param = 'test';
        }
    }

    class NullableTypeConstructor {
        public ?ServiceA $a = null;
        public ?int $b = null;

        public function __construct(?ServiceA $a = null, ?int $b = 42) {
            $this->a = $a;
            $this->b = $b;
        }
    }

    class ServiceA {
    }
    class ServiceB {
        public $a;
        public function __construct(ServiceA $a) {
            $this->a = $a;
        }
    }

    class ServiceC {
        public $b;
        public function __construct(ServiceB $b) {
            $this->b = $b;
        }
    }

    class ServiceD {
        public $a;
        public $b;
        public $c;
        public function __construct(ServiceB $b, ServiceC $c, ServiceA $a) {
            $this->a = $a;
            $this->b = $b;
            $this->c = $c;
        }
    }

    class ServiceContainer implements \Psr\Container\ContainerInterface {
        public array $services = [];
        public function get($id) {
            return $this->services[$id] ?? null;
        }
        public function has($id): bool {
            return isset($this->services[$id]);
        }
    }
}
