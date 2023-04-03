<?php

namespace App\DataFixtures;

use App\Entity\Lang;
use App\Entity\LangHasProjet;
use App\Entity\LangHasUser;
use App\Entity\Projet;
use App\Entity\TraductionSource;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Intl\Languages;
use Datetime;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $langages = Languages::getLanguageCodes();
        foreach ($langages as $langage){
            $new_langage = new Lang();
            $new_langage->setCode($langage);
            $new_langage->setName(Languages::getName($langage, 'fr'));
            $manager->persist($new_langage);

            $manager->flush();
        }

        $projet = new Projet();
        $projet->setUserId(1);
        $projet->setLangCode('fr');
        $projet->setName('Premier projet');
        $projet->setActive(true);
        $projet->setTraduit(false);
        $projet->setCreationDate(new Datetime);
        $manager->persist($projet);

        $manager->flush();

        $projet = new Projet();
        $projet->setUserId(1);
        $projet->setLangCode('en');
        $projet->setName('Second projet');
        $projet->setActive(true);
        $projet->setTraduit(false);
        $projet->setCreationDate(new Datetime);
        $manager->persist($projet);

        $manager->flush();

        $projet = new Projet();
        $projet->setUserId(1);
        $projet->setLangCode('fr');
        $projet->setName('Projet désactiver');
        $projet->setActive(false);
        $projet->setTraduit(false);
        $projet->setCreationDate(new Datetime);
        $manager->persist($projet);

        $manager->flush();

        $source = new TraductionSource();
        $source->setProjetId(1);
        $source->setSource('Voici notre premiere source à traduire');
        $source->setTraduit(false);
        $source->setBloque(false);
        $manager->persist($source);
        $manager->flush();

        $source = new TraductionSource();
        $source->setProjetId(2);
        $source->setSource('Our sources');
        $source->setTraduit(false);
        $source->setBloque(false);
        $manager->persist($source);
        $manager->flush();

        $source = new TraductionSource();
        $source->setProjetId(3);
        $source->setSource('une source invisible');
        $source->setTraduit(false);
        $source->setBloque(false);
        $manager->persist($source);
        $manager->flush();

        $lang_has_user = new LangHasUser();
        $lang_has_user->setLangCode('en');
        $lang_has_user->setUserId(1);
        $manager->persist($lang_has_user);
        $manager->flush();

        $lang_has_user = new LangHasUser();
        $lang_has_user->setLangCode('fr');
        $lang_has_user->setUserId(1);
        $manager->persist($lang_has_user);
        $manager->flush();

        $lang_has_user = new LangHasUser();
        $lang_has_user->setLangCode('pt');
        $lang_has_user->setUserId(1);
        $manager->persist($lang_has_user);
        $manager->flush();

        $lang_has_projet = new LangHasProjet();
        $lang_has_projet->setIdProjet(1);
        $lang_has_projet->setLang('en');
        $manager->persist($lang_has_projet);
        $manager->flush();

        $lang_has_projet = new LangHasProjet();
        $lang_has_projet->setIdProjet(2);
        $lang_has_projet->setLang('fr');
        $manager->persist($lang_has_projet);
        $manager->flush();

        $lang_has_projet = new LangHasProjet();
        $lang_has_projet->setIdProjet(2);
        $lang_has_projet->setLang('pt');
        $manager->persist($lang_has_projet);
        $manager->flush();
    }
}
