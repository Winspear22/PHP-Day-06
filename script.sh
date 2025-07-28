#!/bin/bash

# Chemin de base de ton projet unique
BASE_DIR="ex"

for EX in ex01 ex02 ex03 ex04 ex05 ex06 ex07
do
    SRC_DIR="${EX}/templates"
    DST_DIR="${BASE_DIR}/templates/e${EX:2:2}"

    # Si le dossier source existe
    if [ -d "$SRC_DIR" ]; then
        echo "Copie de $SRC_DIR vers $DST_DIR"
        mkdir -p "$DST_DIR"
        # Copie tout le contenu du dossier source vers le dossier cible
        cp -r $SRC_DIR/* "$DST_DIR/"
    else
        echo "Dossier $SRC_DIR introuvable, on saute."
    fi
done

echo "Tous les templates ont été copiés !"
