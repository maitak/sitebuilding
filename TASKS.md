# Tasklist

1. ~~Create a survey in Drupal 8 that can list a number of questions~~
2. ~~A survey can be created by the client and questions added to it~~
3. ~~Questions can be created by the client and added to surveys~~
4. ~~Questions can have an unlimited number of answers which are composed of free text and a score number.~~
5. A frontend developer must be able to theme the survey without having to log into drupal
6. ~~Creating and editing of surveys can be easily done by non-tech people~~

## Sitebuilding

I have created the Survey content type. In this content type, I added the Question Set paragraph type.  
The Question Set contains a set of questions which each contain an Answer Set paragraph type.  
An Answer Set paragraph type contains a set of answers.  
For each Answer - a score, the answer text and a Discovery Set paragraph type can be added.  
A Discovery Set paragraph type can contain multiple discoveries which each contain just the Discovery text.  

## Twig

I have created the `node--survey.html.twig` file which can be used to add a `<form>` element around the `{{ content }}`  
The `paragraph--[~PARAGRAPH NAME~].html.twig` files contain the list of fields added to each paragraph.  
The `field/field--paragraph--field-[~FIELD NAME~].html.twig` files are the different custom fields added to each paragraph.  

## TODO

### Frontend Tasks
1. Add field variables to each twig file
    - themes/beaker/templates/field/field--paragraph--field-answer.html.twig
    - themes/beaker/templates/field/field--paragraph--field-discovery.html.twig
    - themes/beaker/templates/field/field--paragraph--field-question-answer-set--component.html.twig
    - themes/beaker/templates/field/field--paragraph--field-score.html.twig
    - themes/beaker/templates/field/field--paragraph--field-title--component.html.twig
2. SASS files to be created to theme twig files
3. Post form with a JavaScript module

### Backend Tasks
1. Calculate end of survey score
