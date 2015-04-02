<?php

namespace Arall\CMSDiff\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Arall\CMSDiff\Matcher;

class WebsiteMatch extends Command
{
    public function configure()
    {
        $this
            ->setName('website:match')
            ->setDescription('Get website CMS version')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Website url (http://www.joomla.org/)'
            )
            ->addArgument(
                'json',
                InputArgument::REQUIRED,
                'Path to JSON map file (generated with repository:map)'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getArgument('url');
        $jsonPath = $input->getArgument('json');

        // Load Json file
        if (!file_exists($jsonPath)) {
            return $output->writeln('<error>Data file not found: '.$jsonPath.'</error>');
        }
        $json = '';
        $gzo = gzopen($jsonPath, "r");
        while ($line = gzgets($gzo, 1024)) {
            $json .= $line;
        }
        gzclose($gzo);

        try {
            $matcher = new Matcher($url, $json, $output);
            $versions = $matcher->match();

        } catch (\Exception $e) {
            return $output->writeln('<error>'.$e->getMessage().'</error>');
        }

        $output->writeln('<info>Possible product version(s): '.implode(', ', $versions).'</info>');
        $output->writeln('');
    }
}
