<?php

namespace N98\Magento\Command\GiftCard;

use Magento\GiftCardAccount\Model\Giftcardaccount;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends AbstractGiftCardCommand
{
    /**
     * Setup
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('giftcard:info')
            ->addArgument('code', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Gift card code')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            )
            ->setDescription('Get gift card account information by code');

        $help = <<<HELP
Get gift card account information by code
HELP;
        $this->setHelp($help);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $this->setAdminArea();

        $card = $this->getGiftcard($input->getArgument('code'));
        if (!$card->getId()) {
            $output->writeln('<error>No gift card found for that code</error>');
            return;
        }
        
        $data = array(
            array('Gift Card Account ID', $card->getId()),
            array('Code', $card->getCode()),
            array('Status', Giftcardaccount::STATUS_ENABLED == $card->getStatus() ? 'Enabled' : 'Disabled'),
            array('Date Created', $card->getDateCreated()),
            array('Expiration Date', $card->getDateExpires()),
            array('Website ID', $card->getWebsiteId()),
            array('Remaining Balance', $card->getBalance()),
            array('State', $card->getStateText()),
            array('Is Redeemable', $card->getIsRedeemable()),
        );
        
        $this->getHelper('table')
            ->setHeaders(array('Name', 'Value'))
            ->setRows($data)
            ->renderByFormat($output, $data, $input->getOption('format'));
    }
}
