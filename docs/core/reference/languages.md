# Languages

## Overview

Lunar supports multiple languages. By default, Lunar is set to install a default language of "en" (English). However, this can be changed to whatever language you would like to use.

Languages allow data in Lunar models to be translated, such as attributes on Products and Collections.


### Model

|Field|Description|
|:-|:-|
|`code`|Typically the ISO 2 character language code, e.g. `en`|
|`name`|Descriptive name, e.g. `English`|
|`default`|A boolean specifying the default language for Lunar|

::: warning
There should only ever be one default language. Setting more than one language as default will likely break stuff!
:::