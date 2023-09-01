Evolution:
This content evolves
- The default
- This content will automatically be visible in future versions

This content is unique to each version
- This content will not be visible in future versions

Versions:
This content exists in:
Major Versions: [D] [E] [BKS] [Pres] (min 1)

For each Major Version, you can select:
In every version [Static Content]
In specific versions [Dynamic Content]

When "In specific versions" is selected, you can see all minor and patch versions and select which ones to include (and it indicates which ones it already exists in).

Minor & Patch Versions:

Then we just need ways to relate content to other versions from other versions...
OR: What if we just needed to make it easier for content to be duplicated/controlled across versions
and things should only ever reference content inside their own version?

A browseable version (that gets a table is a full Major.Minor.Patch)

*** 
Synchronized Content with other Major Versions

Force them to explicitly relate minor versions to each other when they are from different Major versions. For example:

BKS.1.0 is related to E.10.0

What this would do is allow Content that for content that "exists in specific versions" to know where to look for other versions of itself.

%%%% Options on versioned %%%%%
----
[] - This content evolves
[] - This content is unique to each version
----
Only affects replication logic on new version creation
----

----
[] - In every version [Static Content] (Major Versions only)
[] - In specific versions [Dynamic Content] (Minor and Patch Versions)
----
When "In every version" is selected you can see all Major Versions you want the content to live in.

When "In specific versions" is selected you can only see the explicitly related Minor & Patch Versions from other Major Versions.
----

----
[] - Synchronized
----
Determines if the content should be synchronized with the other explicitly related versions
----

How to most efficiently explicitly versions to each other?????