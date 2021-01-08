<?php

namespace JMS\TwigJsBundle\Tests\TwigJs\Compiler;

use JMS\TwigJsBundle\TwigJs\Compiler\TransFilterCompiler;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\LoaderInterface;

class TransFilterCompilerTest extends BaseTestCase
{
    private $translator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = new Translator('en');

        $loader = $this->getMockBuilder(LoaderInterface::class)
            ->getMockForAbstractClass();

        $loader
            ->method('load')
            ->willReturnCallback(function ($messages, $locale, $domain) {
                $catalogue = new MessageCatalogue($locale);
                $catalogue->add($messages, $domain);

                return $catalogue;
            });
        $this->translator->addLoader('my', $loader);

        $this->compiler->addFilterCompiler(new TransFilterCompiler($this->translator));
        $this->env->addExtension(new TranslationExtension($this->translator));
    }

    public function testCompile()
    {
        $this->compiler->setDefine('locale', 'de');
        $this->addMessages(['foo' => 'bar'], 'messages', 'de');

        self::assertStringContainsString('sb.append("bar");', $this->compile('{{ "foo"|trans|raw }}'));
    }

    public function testCompileWithParameters()
    {
        $this->compiler->setDefine('locale', 'en');
        $this->addMessages(['remaining' => '%nb% remaining']);

        self::assertStringContainsString(
            'sb.append(twig.filter.replace("%nb% remaining", {"%nb%": ("nb" in context ? context["nb"] : null)}));',
            $this->compile('{{ "remaining"|trans({"%nb%": nb})|raw }}')
        );
    }

    public function testCompileDynamicTranslations()
    {
        $this->compiler->setDefine('locale', 'en');

        self::assertStringContainsString('this.env_.filter("trans",', $this->compile('{{ foo|trans }}'));
        self::assertStringContainsString('this.env_.filter("trans",', $this->compile('{{ "foo"|trans({}, bar) }}'));
    }

    public function testCompileWhenNoLocaleIsSet()
    {
        $this->addMessages(['foo' => 'bar']);
        self::assertStringContainsString('this.env_.filter("trans",', $this->compile('{{ "foo"|trans }}'));
    }

    protected function compile($source): string
    {
        return parent::compile($source, 'index.twig');
    }

    private function addMessages(array $messages, $domain = 'messages', $locale = 'en')
    {
        $this->translator->addResource('my', $messages, $locale, $domain);
    }
}