# rector-move-property-into-class
Quick repo for a custom rector rule. 


## usage

Copy the rules directory  into your own project f.x. into rector/rules and adjust the autoload settings like this:

```composer.json:
{
 "autoload": {
    "psr-4": {
      "App\\": "src/",
      "Rector\\ParameterAnnotation\\": "rector/rules/ParameterAnnotation",
      "Rector\\Tests\\ParameterAnnotation\\": "rector/rules-tests/ParameterAnnotation",
    }
  },
}

```
