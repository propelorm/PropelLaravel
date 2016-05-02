<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Illuminate\Console\Application;

class CommandTestCase extends TestCase
{
    protected function executeCommand(Command $command, $string = null)
    {
        $artisan = new Application($this->app, $this->app->make('events'), $this->app->version());
        $artisan->add($command);

        $commandTester = new CommandTester($command);

        if ($string) {
            /** @var \Symfony\Component\Console\Helper\QuestionHelper $question_helper */
            $question_helper = $command->getHelper('question');
            $stream = $this->getInputStream($string);
            $question_helper->setInputStream($stream);

            $command->getHelperSet()->set($question_helper, 'question');
        }

        $res = $commandTester->execute(['command' => $command->getName()], ['interactive' => true]);

        $this->assertTrue(
            0 === $res,
            sprintf('Command «%s» returned non-zero return code executed by CommandTester. Returned content: %s', $command->getName(), $commandTester->getDisplay(true))
        );

        return $commandTester->getDisplay();
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }
}