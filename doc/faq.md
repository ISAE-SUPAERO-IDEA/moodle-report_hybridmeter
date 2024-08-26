# Foire aux questions

[Retour README](../README.md)

**Les indicateurs mesurent-ils vraiment tous les cours hybrides de l'établissement ?** 
---------------------------------------------------------------------------------------

Nos établissements se sont pour la plupart structurés autour de la plateforme Moodle, comme portail unique des enseignements. Ainsi, les enseignants sont assez poussés à utiliser les activités venant directement dans la plateforme. Le plugin ne fait à ce stade que compter les activités et leurs usages par les étudiants dans les cours. Il est tout à fait possible que certains cours n'aient pas utilisé d'activités Moodle et soient très hybride. 

**Les indicateurs sont-ils diffusés en dehors de la plateforme ?**
------------------------------------------------------------------

Seul le profil administrateur de votre plateforme aura accès aux résultats des calculs, que ce soit les résultats globaux ou le détail par cours. Aucune information n'est stockée dans la base de donnée locale, et aucune information ne sort de la plateforme.

**J'ai détecté un bug, j'ai une suggestion, comment puis-je le signaler ?**
---------------------------------------------------------------------------

*   Utiliser le bouton "Support" (en jaune, en haut à droite des pages du site), permettant la remontée de suggestions à l'équipe de développement. Vous pouvez aussi cliquer sur le bouton suivant.

[Support](https://forms.clickup.com/f/2f5v0-8508/5SDCGICT8X4L037TAF)

**Nous utilisons un plugin spécifique, qu'il faudrait prendre en compte dans les calculs**
------------------------------------------------------------------------------------------

Vous pouvez proposer ce plugin en utilisant le bouton de Support (en haut à droite des pages de ce site), permettant la remontée de suggestions à l'équipe de développement.

**Comment puis-je être averti de nouvelles versions ?**
-------------------------------------------------------

Inscrivez votre email professionnel avec le bouton "Inscription" en haut à droite.

Quel établissement peut installer le plugin et l'utiliser ?
-----------------------------------------------------------

Tout établissement d'enseignement supérieur peut installer le plugin.

**Y a t il une garantie sur ce logiciel ? sur le service ?**
------------------------------------------------------------

Le logiciel est livré gratuitement, avec le code source et cette documentation selon une approche de meilleur effort de la part de l'équipe de développement. Il vous appartient de vérifier que le code informatique livré correspond bien à vos attentes. Le logiciel est utilisable en l'état, modifiable, sans aucune garantie de fonctionnement sur votre plateforme.

L'équipe de développement n'est pas responsable des problèmes qui pourraient être causés par l'installation et l'exécution du code sur votre plateforme.

  

### Le plugin effectue des calculs mais les indicateurs restent nuls

  

*   Vérifiez que la configuration du plugin (list d'exclusions et période à prendre en compte) est correcte.
*   Le plugin se base sur le rôle "student" pour identifier les étudiants. si vous utilisez un autre rôle pour identifier les étudiants le plugin ne peut pas les identifier. il sera possible de choisir le rôle identifiant les étudiants dans une prochaine version du plugin.
*   Le plugin se base sur l'existence d'entrées de log Moodle (en plus de la liste d'exclusions) pour identifier les cours actifs. Si vous purgez les logs, le plugin ne fonctionnera pas pour les périodes antérieures ou il n'y a plus de logs.

  

  

  

[SITE WEB HYBRIDMETER](https://online.isae-supaero.fr/hybridmeter)