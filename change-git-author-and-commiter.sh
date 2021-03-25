#!/usr/bin/env bash

set -o errexit


# @info:    Prints warning messages
# @args:    warning-message
echoWarn ()
{
  printf "\033[0;33m$1\033[0m"
}

printf "\033[0;33m \n Hey, you need to modify this script to make it work\033[0m"
printf "\033[0;33m \n Change the repository url in line \033[0m"
printf "\033[0;33m \n Change OLD_EMAIL/NEW_EMAIL/NEW_NAME \n \033[0m"
printf "\nDo you want to continue [y/n]? "
read CONTINUE

if [ "$CONTINUE" != "y" ];
then
    echoWarn "Aborting"
    exit
fi

# @info:    Prints info messages
# @args:    info-message
echoInfo ()
{
  printf "\033[1;34m$1\033[0m\n"
}


# @info:    Prints success messages
# @args:    success-message
echoSuccess ()
{
  printf "\033[0;32m$1\033[0m"
}

echoInfo "\nPlease review the information carefully.\nOnce we start, there's no way back!"

printf "\nDo you want to continue [y/n]? "
read CONTINUE

if [ "$CONTINUE" != "y" ];
then
    echoWarn "Aborting"
    exit
fi

echoInfo "Continue script execution"

DIR_NAME="git-author-replace-"$(date +%s)


git clone --bare git@github.com:UserName/repository.git $DIR_NAME
cd $DIR_NAME


git filter-branch --env-filter '

if [ "$GIT_COMMITTER_EMAIL" = "OLD_EMAIL" ]
then
    export GIT_COMMITTER_NAME="NEW_NAME"
    export GIT_COMMITTER_EMAIL="NEW_EMAIL"
fi
if [ "$GIT_AUTHOR_EMAIL" = "OLD_EMAIL" ]
then
    export GIT_AUTHOR_NAME="NEW_NAME"
    export GIT_AUTHOR_EMAIL="NEW_EMAIL"
fi
' --tag-name-filter cat -- --branches --tags

git push --force --tags origin 'refs/heads/*'

cd ..
rm -rf $DIR_NAME

echoSuccess "Done. Operation successfully completed !"
