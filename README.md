# plugin-gcs

#### OSClass Google Cloud Storage plugin

A Plugin for [OSClass](https://osclass.org/) that allows storage of media assets
in a [Google Cloud Storage](https://cloud.google.com/storage/)
bucket much like the [amazons3 plugin](https://github.com/osclass/plugin-amazons3/tree/master/amazons3).
Written originally for OSClass 3.6.1. Not sure what the versioning support is
for OSClass as far as older versions. Your mileage may vary, etc. etc.

The assumption is made that you know how PHP development and all the associated
things to get the code to work within OSClass. I certainly don't and kind of dislike
PHP, so this is probably a serious hack to get working. I just want to use OSClass
inside of Kubernetes and need some sort of **network based storage** to
properly handle the media content.

There is also the assumption that you know how [Google Cloud Platform](https://cloud.google.com/)
works and how to create a [Service Account](https://cloud.google.com/docs/authentication),
assign permission, etc. etc. The plugin will use the `GOOGLE_APPLICATION_CREDENTIALS`
environment variable to find the Service Account credentials for authentication
against the Cloud Services.
