<?php


namespace JMS\TwigJsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TwigJs\CompileRequest;
use TwigJs\CompileRequestHandler;

/**
 * Compiling Twig into pure javascript
 *
 * @author Oleg Andreyev <oleg@andreyev.lv>
 */
class CompileTwigCommand extends Command
{
    /**
     * @var CompileRequestHandler
     */
    private $compileRequestHandler;

    public function __construct(string $name = null, CompileRequestHandler $compileRequestHandler)
    {
        parent::__construct($name);
        $this->compileRequestHandler = $compileRequestHandler;
    }

    protected function configure()
    {
        $this
            ->setDefinition([
                new InputArgument('name', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The template name')
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($input->getArgument('name') as $name) {
            $compileRequest = new CompileRequest($name, null);
            $output->write($this->compileRequestHandler->process($compileRequest));
        }
    }
}
