<?php

namespace JMS\TwigJsBundle\TwigJs\Compiler;

use Symfony\Contracts\Translation\TranslatorInterface as ContractsTranslatorInterface;
use Symfony\Component\Translation\TranslatorInterface as TranslationTranslatorInterface;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use TwigJs\JsCompiler;
use TwigJs\FilterCompilerInterface;

class TransFilterCompiler implements FilterCompilerInterface
{
    private $translator;
    private $loadCatalogueRef;
    private $catalogueRef;

    /**
     * TransFilterCompiler constructor.
     *
     * @param ContractsTranslatorInterface|TranslationTranslatorInterface $translator
     */
    public function __construct($translator)
    {
        if (class_exists(ContractsTranslatorInterface::class) && !$translator instanceof ContractsTranslatorInterface) {
            throw new \InvalidArgumentException(sprintf('Please supply %s', ContractsTranslatorInterface::class));
        }

        if (class_exists(TranslationTranslatorInterface::class) && !$translator instanceof TranslationTranslatorInterface) {
            throw new \InvalidArgumentException(sprintf('Please supply %s', TranslationTranslatorInterface::class));
        }

        $this->translator = $translator;
    }

    public function getName()
    {
        return 'trans';
    }

    public function compile(JsCompiler $compiler, FilterExpression $node)
    {
        if (!($locale = $compiler->getDefine('locale'))) {
            return false;
        }

        // unfortunately, the Translation component does not provide a better
        // way to retrieve these
        $this->loadCatalogueRef = new \ReflectionMethod($this->translator, 'loadCatalogue');
        $this->loadCatalogueRef->setAccessible(true);
        $this->catalogueRef = new \ReflectionProperty($this->translator, 'catalogues');
        $this->catalogueRef->setAccessible(true);

        // ignore dynamic messages, we cannot resolve these
        // users can still apply a runtime trans filter to do this
        $subNode = $node->getNode('node');
        if (!$subNode instanceof ConstantExpression) {
            return false;
        }

        $id = $subNode->getAttribute('value');
        $domain = 'messages';
        $hasParams = false;

        $arguments = $node->getNode('arguments');
        if (count($arguments) > 0) {
            $hasParams = count($arguments->getNode(0)) > 0;

            if ($arguments->hasNode(1)) {
                $domainNode = $arguments->getNode(1);

                if (!$domainNode instanceof ConstantExpression) {
                    return false;
                }

                $domain = $domainNode->getAttribute('value');
            }
        }

        $catalogue = $this->getCatalogue($locale);

        if (!$hasParams) {
            $compiler->string($catalogue->get($id, $domain));

            return;
        }

        $compiler
            ->raw('twig.filter.replace(')
            ->string($catalogue->get($id, $domain))
            ->raw(", ")
            ->subcompile($arguments->getNode(0))
            ->raw(')')
        ;
    }

    private function getCatalogue($locale)
    {
        $this->loadCatalogueRef->invoke($this->translator, $locale);
        $catalogues = $this->catalogueRef->getValue($this->translator);

        return $catalogues[$locale];
    }
}
