<?php
/**
 * Copyright (c) Since 2020 Friends of Presta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Friends of Presta <infos@friendsofpresta.org>
 * @copyright since 2020 Friends of Presta
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 */

declare(strict_types=1);

namespace FriendsOfPresta\BaseModuleInstaller;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class GrumphpConfigurationCommand extends Command
{
    public function configure(): void
    {
        $this->setName('fop:grumphpinit');
        $this->addOption('base-path', 'd', InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $base_path = $input->getOption('base-path');
        if (!is_string($base_path)) {
            dump($base_path);
            throw new \Exception('Invalid Option --base-path. See above.');
        }

        $grumphp_yaml_path = (string) $base_path . 'grumphp.yml';
        $yaml = Yaml::parseFile($grumphp_yaml_path);
        $output->writeln('A path to a Prestashop installation is needed by phpstan.');
        do {
            $question = new Question('<question> Where is the reference Prestashop ? </question> ');
            $questioner = $this->getHelper('question');
            $path = $questioner->ask($input, $output, $question);
            $valid_path = empty($path) || realpath($path);
            if (!$valid_path) {
                $output->write('<fg=red>This path is not valid. </>');
                $output->writeln('Provide a valid one (or leave empty to skip)');
            }
        } while (!$valid_path);

        if (!$path) {
            $output->writeln('No _PS_ROOT_DIR_ path provided. grumphp configuration not modified.');

            return 1;
        }

        $yaml['grumphp']['environment']['variables']['_PS_ROOT_DIR_'] = $path;
        if (!file_put_contents($grumphp_yaml_path, Yaml::dump($yaml, 2, 2))) {
            throw new \Exception("Failed writing modified yaml to $grumphp_yaml_path.");
        }
        $output->writeln('<info> grumphp.yml modified </info>');
        $output->writeln(sprintf('grump.yml path : %s.', $grumphp_yaml_path), OutputInterface::VERBOSITY_VERBOSE);

        return Command::SUCCESS; /* @phpstan-ignore-line */
    }
}
