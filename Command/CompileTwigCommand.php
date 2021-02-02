<?php


namespace JMS\TwigJsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
                new InputArgument('source', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Template source'),
                new InputOption('name', null, InputOption::VALUE_OPTIONAL, 'Template name')
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (count($input->getArgument('source')) > 0) {
            foreach ($input->getArgument('source') as $name) {
                $compileRequest = new CompileRequest($name, null);
                $output->write($this->compileRequestHandler->process($compileRequest));
            }

            return;
        }

        if (!$input->hasOption('name')) {
            throw new InvalidOptionException("Option 'name' when using stdin as source");
        }

        $source = $this->readFromStdin();
        $compileRequest = new CompileRequest($input->getOption('name'), $source);
        $output->write($this->compileRequestHandler->process($compileRequest));
    }

    private function readFromStdin(): string
    {
        return stream_get_contents(STDIN);
    }
}
