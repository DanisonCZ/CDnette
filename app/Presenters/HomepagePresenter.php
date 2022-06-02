<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{

	private Nette\Database\Explorer $database;


	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;

        $paginator = new Nette\Utils\Paginator;
        $paginator->setPage(1); // číslo aktuální stránky
        $paginator->setItemsPerPage(30); // počet položek na stránce
        $paginator->setItemCount(356); // celkový počet položek, je-li znám
	}

    public function startup(): void
    {
	    parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }


    public function renderDefault(): void
    {
        $this->template->dbcd = $this->database
            ->table('cd')
            ->order('id');
    }

    protected function createComponentCommentForm(): Form
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('nazevCD', 'Název: ')
            ->setRequired('Zadejte název CD');

        $form->addInteger('delkaCD', 'Délka: ')
            ->setRequired('Zadejte délku CD')
            ->addRule(
                [HomepagePresenter::class, 'validateOver0'],
                'Délka musí být vetší než 0'
            );

        $form->addText('autorCD', 'Autor: ')
            ->setRequired('Zadejte dautora CD');

        $form->addText('datumCD', 'Datum vydání: ')
            ->setType('Date')
            ->setRequired('Zadejte datum vydání CD');

        $form->addSubmit('send', 'Přidat');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

	public static function validateOver0($input): bool
	{
		return $input->getValue() > 0;
	}


    public function formSucceeded(\stdClass $data): void
    {
        //$postId = $this->getParameter('postId');

        if ( is_int($data->delkaCD) && ($data->delkaCD)>0 )
        {
        $this->database->table('cd')->insert([
            'nazev' => $data->nazevCD,
            'delka' => $data->delkaCD,
            'autor' => $data->autorCD,
            'datum' => $data->datumCD,
        ]);
            $this->flashMessage('Záznam přidán', 'success');
        } else {
            $this->flashMessage('špatný formát délky');
        }
        $this->redirect('this');
    }

}