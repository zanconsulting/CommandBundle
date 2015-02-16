<?php


namespace Zan\CommandBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Utility methods for command line applications
 */
abstract class ZanAbstractCommand extends ContainerAwareCommand
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Overridden to allow easier access to $input and $output. Maybe not great
     * design, but so convenient...
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int The command exit code
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        return parent::run($input, $output);
    }

    /**
     * Wrapper around $output->writeln that supports printf-style formatting
     *
     * @param $line
     * @param ...
     */
    protected function writelnf($line)
    {
        $arguments = func_get_args();

        $output = call_user_func_array('sprintf', $arguments);

        $this->output->writeln($output);
    }

    /**
     * Executes another Application command
     *
     * @param OutputInterface $output
     * @param                 $commandName
     * @param array           $arguments
     */
    protected function runOtherCommand(OutputInterface $output, $commandName, $arguments = array())
    {
        $command = $this->getApplication()->get($commandName);

        // The name of the command must be one of the arguments
        $arguments = array_merge(array('command' => $commandName), $arguments);

        // And they must be of type ArrayInput
        $arguments = new ArrayInput($arguments);

        $command->run($arguments, $output);
    }

    /**
     * Call during the execution of a command to indicate an error occurred
     * during the execution.
     *
     * @param OutputInterface $output
     * @param string          $msg
     */
    protected function indicateError(OutputInterface $output, $msg)
    {
        $output->writeln("");
        $output->writeln("<error>$msg</error>");
    }

    /**
     * Prints out an error message an immediately exits
     *
     * @param OutputInterface $output
     * @param                 $msg
     * @param int             $errorCode
     */
    protected function exitWithError(OutputInterface $output, $msg, $errorCode = 1)
    {
        $this->indicateError($output, $msg);
        exit($errorCode);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEm()
    {
        return $this->getContainer()->get("doctrine")->getManager();
    }

    /**
     * Uses the EntityManager to persist the given Entity (or group of Entities)
     * and immediately save to the database.
     *
     * @param object|array $data
     */
    protected function persistAndFlush($data)
    {
        $em = $this->getEm();

        if (is_array($data) || $data instanceof \Traversable) {
            foreach ($data as $entity) {
                $em->persist($entity);
            }
        } else {
            $em->persist($data);
        }

        $em->flush();
    }
}