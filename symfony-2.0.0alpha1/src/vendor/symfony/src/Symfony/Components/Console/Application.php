<?php

namespace Symfony\Components\Console;

use Symfony\Components\Console\Input\InputInterface;
use Symfony\Components\Console\Input\ArgvInput;
use Symfony\Components\Console\Input\ArrayInput;
use Symfony\Components\Console\Input\InputDefinition;
use Symfony\Components\Console\Input\InputOption;
use Symfony\Components\Console\Input\InputArgument;
use Symfony\Components\Console\Output\OutputInterface;
use Symfony\Components\Console\Output\Output;
use Symfony\Components\Console\Output\ConsoleOutput;
use Symfony\Components\Console\Command\Command;
use Symfony\Components\Console\Command\HelpCommand;
use Symfony\Components\Console\Command\ListCommand;
use Symfony\Components\Console\Helper\HelperSet;
use Symfony\Components\Console\Helper\FormatterHelper;
use Symfony\Components\Console\Helper\DialogHelper;

/*
 * This file is part of the symfony framework.
 *
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * An Application is the container for a collection of commands.
 *
 * It is the main entry point of a Console application.
 *
 * This class is optimized for a standard CLI environment.
 *
 * Usage:
 *
 *     $app = new Application('myapp', '1.0 (stable)');
 *     $app->addCommand(new SimpleCommand());
 *     $app->run();
 *
 * @package    symfony
 * @subpackage console
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class Application
{
  protected $commands;
  protected $aliases;
  protected $application;
  protected $wantHelps = false;
  protected $runningCommand;
  protected $name;
  protected $version;
  protected $catchExceptions;
  protected $autoExit;
  protected $definition;
  protected $helperSet;

  /**
   * Constructor.
   *
   * @param string  $name    The name of the application
   * @param string  $version The version of the application
   */
  public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
  {
    $this->name = $name;
    $this->version = $version;
    $this->catchExceptions = true;
    $this->autoExit = true;
    $this->commands = array();
    $this->aliases = array();
    $this->helperSet = new HelperSet(array(
      new FormatterHelper(),
      new DialogHelper(),
    ));

    $this->addCommand(new HelpCommand());
    $this->addCommand(new ListCommand());

    $this->definition = new InputDefinition(array(
      new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),

      new InputOption('--help',           '-h', InputOption::PARAMETER_NONE, 'Display this help message.'),
      new InputOption('--quiet',          '-q', InputOption::PARAMETER_NONE, 'Do not output any message.'),
      new InputOption('--verbose',        '-v', InputOption::PARAMETER_NONE, 'Increase verbosity of messages.'),
      new InputOption('--version',        '-V', InputOption::PARAMETER_NONE, 'Display this program version.'),
      new InputOption('--color',          '-c', InputOption::PARAMETER_NONE, 'Force ANSI color output.'),
      new InputOption('--no-interaction', '-n', InputOption::PARAMETER_NONE, 'Do not ask any interactive question.'),
    ));
  }

  /**
   * Runs the current application.
   *
   * @param InputInterface  $input  An Input instance
   * @param OutputInterface $output An Output instance
   *
   * @return integer 0 if everything went fine, or an error code
   */
  public function run(InputInterface $input = null, OutputInterface $output = null)
  {
    if (null === $input)
    {
      $input = new ArgvInput();
    }

    if (null === $output)
    {
      $output = new ConsoleOutput();
    }

    try
    {
      $statusCode = $this->doRun($input, $output);
    }
    catch (\Exception $e)
    {
      if (!$this->catchExceptions)
      {
        throw $e;
      }

      $this->renderException($e, $output);
      $statusCode = $e->getCode();

      $statusCode = is_numeric($statusCode) && $statusCode ? $statusCode : 1;
    }

    if ($this->autoExit)
    {
      // @codeCoverageIgnoreStart
      exit($statusCode);
      // @codeCoverageIgnoreEnd
    }
    else
    {
      return $statusCode;
    }
  }

  /**
   * Runs the current application.
   *
   * @param InputInterface  $input  An Input instance
   * @param OutputInterface $output An Output instance
   *
   * @return integer 0 if everything went fine, or an error code
   */
  public function doRun(InputInterface $input, OutputInterface $output)
  {
    $name = $input->getFirstArgument('command');

    if (true === $input->hasParameterOption(array('--color', '-c')))
    {
      $output->setDecorated(true);
    }

    if (true === $input->hasParameterOption(array('--help', '-H')))
    {
      if (!$name)
      {
        $name = 'help';
        $input = new ArrayInput(array('command' => 'help'));
      }
      else
      {
        $this->wantHelps = true;
      }
    }

    if (true === $input->hasParameterOption(array('--no-interaction', '-n')))
    {
      $input->setInteractive(false);
    }

    if (true === $input->hasParameterOption(array('--quiet', '-q')))
    {
      $output->setVerbosity(Output::VERBOSITY_QUIET);
    }
    elseif (true === $input->hasParameterOption(array('--verbose', '-v')))
    {
      $output->setVerbosity(Output::VERBOSITY_VERBOSE);
    }

    if (true === $input->hasParameterOption(array('--version', '-V')))
    {
      $output->writeln($this->getLongVersion());

      return 0;
    }

    if (!$name)
    {
      $name = 'list';
      $input = new ArrayInput(array('command' => 'list'));
    }

    // the command name MUST be the first element of the input
    $command = $this->findCommand($name);

    $this->runningCommand = $command;
    $statusCode = $command->run($input, $output);
    $this->runningCommand = null;

    return is_numeric($statusCode) ? $statusCode : 0;
  }

  /**
   * Set a helper set to be used with the command.
   *
   * @param HelperSet $helperSet The helper set
   */
  public function setHelperSet(HelperSet $helperSet)
  {
    $this->helperSet = $helperSet;
  }

  /**
   * Get the helper set associated with the command
   *
   * @return HelperSet The HelperSet isntance associated with this command
   */
  public function getHelperSet()
  {
    return $this->helperSet;
  }

  /**
   * Gets the InputDefinition related to this Application.
   *
   * @return InputDefinition The InputDefinition instance
   */
  public function getDefinition()
  {
    return $this->definition;
  }

  /**
   * Gets the help message.
   *
   * @return string A help message.
   */
  public function getHelp()
  {
    $messages = array(
      $this->getLongVersion(),
      '',
      '<comment>Usage:</comment>',
      sprintf("  %s [options] command [arguments]\n", $this->getName()),
      '<comment>Options:</comment>',
    );

    foreach ($this->definition->getOptions() as $option)
    {
      $messages[] = sprintf('  %-24s %s  %s',
        '<info>--'.$option->getName().'</info>',
        $option->getShortcut() ? '<info>-'.$option->getShortcut().'</info>' : '  ',
        $option->getDescription()
      );
    }

    return implode("\n", $messages);
  }

  /**
   * Sets whether to catch exceptions or not during commands execution.
   *
   * @param Boolean $boolean Whether to catch exceptions or not during commands execution
   */
  public function setCatchExceptions($boolean)
  {
    $this->catchExceptions = (Boolean) $boolean;
  }

  /**
   * Sets whether to automatically exit after a command execution or not.
   *
   * @param Boolean $boolean Whether to automatically exit after a command execution or not
   */
  public function setAutoExit($boolean)
  {
    $this->autoExit = (Boolean) $boolean;
  }

  /**
   * Gets the name of the application.
   *
   * @return string The application name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Sets the application name.
   *
   * @param string $name The application name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Gets the application version.
   *
   * @return string The application version
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Sets the application version.
   *
   * @param string $version The application version
   */
  public function setVersion($version)
  {
    $this->version = $version;
  }

  /**
   * Returns the long version of the application.
   *
   * @return string The long application version
   */
  public function getLongVersion()
  {
    if ('UNKNOWN' !== $this->getName() && 'UNKNOWN' !== $this->getVersion())
    {
      return sprintf('<info>%s</info> version <comment>%s</comment>', $this->getName(), $this->getVersion());
    }
    else
    {
      return '<info>Console Tool</info>';
    }
  }

  /**
   * Registers a new command.
   *
   * @param string $name The command name
   *
   * @return Command The newly created command
   */
  public function register($name)
  {
    return $this->addCommand(new Command($name));
  }

  /**
   * Adds an array of command objects.
   *
   * @param array  $commands  An array of commands
   */
  public function addCommands(array $commands)
  {
    foreach ($commands as $command)
    {
      $this->addCommand($command);
    }
  }

  /**
   * Adds a command object.
   *
   * If a command with the same name already exists, it will be overridden.
   *
   * @param Command $command A Command object
   *
   * @return Command The registered command
   */
  public function addCommand(Command $command)
  {
    $command->setApplication($this);

    $this->commands[$command->getFullName()] = $command;

    foreach ($command->getAliases() as $alias)
    {
      $this->aliases[$alias] = $command;
    }

    return $command;
  }

  /**
   * Returns a registered command by name or alias.
   *
   * @param string $name The command name or alias
   *
   * @return Command A Command object
   */
  public function getCommand($name)
  {
    if (!isset($this->commands[$name]) && !isset($this->aliases[$name]))
    {
      throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
    }

    $command = isset($this->commands[$name]) ? $this->commands[$name] : $this->aliases[$name];

    if ($this->wantHelps)
    {
      $this->wantHelps = false;

      $helpCommand = $this->getCommand('help');
      $helpCommand->setCommand($command);

      return $helpCommand;
    }

    return $command;
  }

  /**
   * Returns true if the command exists, false otherwise
   *
   * @param string $name The command name or alias
   *
   * @return Boolean true if the command exists, false otherwise
   */
  public function hasCommand($name)
  {
    return isset($this->commands[$name]) || isset($this->aliases[$name]);
  }

  /**
   * Returns an array of all unique namespaces used by currently registered commands.
   *
   * It does not returns the global namespace which always exists.
   *
   * @return array An array of namespaces
   */
  public function getNamespaces()
  {
    $namespaces = array();
    foreach ($this->commands as $command)
    {
      if ($command->getNamespace())
      {
        $namespaces[$command->getNamespace()] = true;
      }
    }

    return array_keys($namespaces);
  }

  /**
   * Finds a registered namespace by a name or an abbreviation.
   *
   * @return string A registered namespace
   */
  public function findNamespace($namespace)
  {
    $abbrevs = static::getAbbreviations($this->getNamespaces());

    if (!isset($abbrevs[$namespace]))
    {
      throw new \InvalidArgumentException(sprintf('There are no commands defined in the "%s" namespace.', $namespace));
    }

    if (count($abbrevs[$namespace]) > 1)
    {
      throw new \InvalidArgumentException(sprintf('The namespace "%s" is ambiguous (%s).', $namespace, $this->getAbbreviationSuggestions($abbrevs[$namespace])));
    }

    return $abbrevs[$namespace][0];
  }

  /**
   * Finds a command by name or alias.
   *
   * Contrary to getCommand, this command tries to find the best
   * match if you give it an abbreviation of a name or alias.
   *
   * @param  string $name A command name or a command alias
   *
   * @return Command A Command instance
   */
  public function findCommand($name)
  {
    // namespace
    $namespace = '';
    if (false !== $pos = strpos($name, ':'))
    {
      $namespace = $this->findNamespace(substr($name, 0, $pos));
      $name = substr($name, $pos + 1);
    }

    $fullName = $namespace ? $namespace.':'.$name : $name;

    // name
    $commands = array();
    foreach ($this->commands as $command)
    {
      if ($command->getNamespace() == $namespace)
      {
        $commands[] = $command->getName();
      }
    }

    $abbrevs = static::getAbbreviations($commands);
    if (isset($abbrevs[$name]) && 1 == count($abbrevs[$name]))
    {
      return $this->getCommand($namespace ? $namespace.':'.$abbrevs[$name][0] : $abbrevs[$name][0]);
    }

    if (isset($abbrevs[$name]) && count($abbrevs[$name]) > 1)
    {
      $suggestions = $this->getAbbreviationSuggestions(array_map(function ($command) use ($namespace) { return $namespace.':'.$command; }, $abbrevs[$name]));

      throw new \InvalidArgumentException(sprintf('Command "%s" is ambiguous (%s).', $fullName, $suggestions));
    }

    // aliases
    $abbrevs = static::getAbbreviations(array_keys($this->aliases));
    if (!isset($abbrevs[$fullName]))
    {
      throw new \InvalidArgumentException(sprintf('Command "%s" is not defined.', $fullName));
    }

    if (count($abbrevs[$fullName]) > 1)
    {
      throw new \InvalidArgumentException(sprintf('Command "%s" is ambiguous (%s).', $fullName, $this->getAbbreviationSuggestions($abbrevs[$fullName])));
    }

    return $this->getCommand($abbrevs[$fullName][0]);
  }

  /**
   * Gets the commands (registered in the given namespace if provided).
   *
   * The array keys are the full names and the values the command instances.
   *
   * @param  string  $namespace A namespace name
   *
   * @return array An array of Command instances
   */
  public function getCommands($namespace = null)
  {
    if (null === $namespace)
    {
      return $this->commands;
    }

    $commands = array();
    foreach ($this->commands as $name => $command)
    {
      if ($namespace === $command->getNamespace())
      {
        $commands[$name] = $command;
      }
    }

    return $commands;
  }

  /**
   * Returns an array of possible abbreviations given a set of names.
   *
   * @param array An array of names
   *
   * @return array An array of abbreviations
   */
  static public function getAbbreviations($names)
  {
    $abbrevs = array();
    foreach ($names as $name)
    {
      for ($len = strlen($name) - 1; $len > 0; --$len)
      {
        $abbrev = substr($name, 0, $len);
        if (!isset($abbrevs[$abbrev]))
        {
          $abbrevs[$abbrev] = array($name);
        }
        else
        {
          $abbrevs[$abbrev][] = $name;
        }
      }
    }

    // Non-abbreviations always get entered, even if they aren't unique
    foreach ($names as $name)
    {
      $abbrevs[$name] = array($name);
    }

    return $abbrevs;
  }

  /**
   * Returns a text representation of the Application.
   *
   * @param string $namespace An optional namespace name
   *
   * @return string A string representing the Application
   */
  public function asText($namespace = null)
  {
    $commands = $namespace ? $this->getCommands($this->findNamespace($namespace)) : $this->commands;

    $messages = array($this->getHelp(), '');
    if ($namespace)
    {
      $messages[] = sprintf("<comment>Available commands for the \"%s\" namespace:</comment>", $namespace);
    }
    else
    {
      $messages[] = '<comment>Available commands:</comment>';
    }

    $width = 0;
    foreach ($commands as $command)
    {
      $width = strlen($command->getName()) > $width ? strlen($command->getName()) : $width;
    }
    $width += 2;

    // add commands by namespace
    foreach ($this->sortCommands($commands) as $space => $commands)
    {
      if (!$namespace && '_global' !== $space)
      {
        $messages[] = '<comment>'.$space.'</comment>';
      }

      foreach ($commands as $command)
      {
        $aliases = $command->getAliases() ? '<comment> ('.implode(', ', $command->getAliases()).')</comment>' : '';

        $messages[] = sprintf("  <info>%-${width}s</info> %s%s", ($command->getNamespace() ? ':' : '').$command->getName(), $command->getDescription(), $aliases);
      }
    }

    return implode("\n", $messages);
  }

  /**
   * Returns an XML representation of the Application.
   *
   * @param string $namespace An optional namespace name
   * @param Boolean $asDom Whether to return a DOM or an XML string
   *
   * @return string|DOMDocument An XML string representing the Application
   */
  public function asXml($namespace = null, $asDom = false)
  {
    $commands = $namespace ? $this->getCommands($this->findNamespace($namespace)) : $this->commands;

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $dom->appendChild($xml = $dom->createElement('symfony'));

    $xml->appendChild($commandsXML = $dom->createElement('commands'));

    if ($namespace)
    {
      $commandsXML->setAttribute('namespace', $namespace);
    }
    else
    {
      $xml->appendChild($namespacesXML = $dom->createElement('namespaces'));
    }

    // add commands by namespace
    foreach ($this->sortCommands($commands) as $space => $commands)
    {
      if (!$namespace)
      {
        $namespacesXML->appendChild($namespaceArrayXML = $dom->createElement('namespace'));
        $namespaceArrayXML->setAttribute('id', $space);
      }

      foreach ($commands as $command)
      {
        if (!$namespace)
        {
          $namespaceArrayXML->appendChild($commandXML = $dom->createElement('command'));
          $commandXML->appendChild($dom->createTextNode($command->getName()));
        }

        $commandXML = new \DOMDocument('1.0', 'UTF-8');
        $commandXML->formatOutput = true;
        $commandXML->loadXML($command->asXml());
        $node = $commandXML->getElementsByTagName('command')->item(0);
        $node = $dom->importNode($node, true);

        $commandsXML->appendChild($node);
      }
    }

    return $asDom ? $dom : $dom->saveXml();
  }

  /**
   * Renders a catched exception.
   *
   * @param Exception       $e      An exception instance
   * @param OutputInterface $output An OutputInterface instance
   */
  public function renderException($e, $output)
  {
    $strlen = function ($string)
    {
      return function_exists('mb_strlen') ? mb_strlen($string) : strlen($string);
    };

    $title = sprintf('  [%s]  ', get_class($e));
    $len = $strlen($title);
    $lines = array();
    foreach (explode("\n", $e->getMessage()) as $line)
    {
      $lines[] = sprintf('  %s  ', $line);
      $len = max($strlen($line) + 4, $len);
    }

    $messages = array(str_repeat(' ', $len), $title.str_repeat(' ', $len - $strlen($title)));

    foreach ($lines as $line)
    {
      $messages[] = $line.str_repeat(' ', $len - $strlen($line));
    }

    $messages[] = str_repeat(' ', $len);

    $output->writeln("\n");
    foreach ($messages as $message)
    {
      $output->writeln("<error>$message</error>");
    }
    $output->writeln("\n");

    if (null !== $this->runningCommand)
    {
      $output->writeln(sprintf('<info>%s</info>', sprintf($this->runningCommand->getSynopsis(), $this->getName())));
      $output->writeln("\n");
    }

    if (Output::VERBOSITY_VERBOSE === $output->getVerbosity())
    {
      $output->writeln('</comment>Exception trace:</comment>');

      // exception related properties
      $trace = $e->getTrace();
      array_unshift($trace, array(
        'function' => '',
        'file'     => $e->getFile() != null ? $e->getFile() : 'n/a',
        'line'     => $e->getLine() != null ? $e->getLine() : 'n/a',
        'args'     => array(),
      ));

      for ($i = 0, $count = count($trace); $i < $count; $i++)
      {
        $class = isset($trace[$i]['class']) ? $trace[$i]['class'] : '';
        $type = isset($trace[$i]['type']) ? $trace[$i]['type'] : '';
        $function = $trace[$i]['function'];
        $file = isset($trace[$i]['file']) ? $trace[$i]['file'] : 'n/a';
        $line = isset($trace[$i]['line']) ? $trace[$i]['line'] : 'n/a';

        $output->writeln(sprintf(' %s%s%s() at <info>%s:%s</info>', $class, $type, $function, $file, $line));
      }

      $output->writeln("\n");
    }
  }

  private function sortCommands($commands)
  {
    $namespacedCommands = array();
    foreach ($commands as $name => $command)
    {
      $key = $command->getNamespace() ? $command->getNamespace() : '_global';

      if (!isset($namespacedCommands[$key]))
      {
        $namespacedCommands[$key] = array();
      }

      $namespacedCommands[$key][$name] = $command;
    }
    ksort($namespacedCommands);

    foreach ($namespacedCommands as $name => &$commands)
    {
      ksort($commands);
    }

    return $namespacedCommands;
  }

  private function getAbbreviationSuggestions($abbrevs)
  {
    return sprintf('%s, %s%s', $abbrevs[0], $abbrevs[1], count($abbrevs) > 2 ? sprintf(' and %d more', count($abbrevs) - 2) : '');
  }
}
