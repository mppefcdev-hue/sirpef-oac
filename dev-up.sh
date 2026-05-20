#!/bin/bash

# Colores para la salida
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}Iniciando servicios de desarrollo...${NC}"

# Función para ejecutar docker-compose
# Uso: ejecutar_docker "directorio" "archivo.yml"
ejecutar_docker() {
    local dir=$1
    local file=$2
    echo -e "\n${GREEN}Carpeta: $dir${NC}"
    
    if [ -d "$dir" ]; then
        cd "$dir" || exit
        if [ -n "$file" ]; then
            if [ -f "$file" ]; then
                echo "Usando archivo: $file"
                docker-compose -f "$file" up -d
            else
                echo -e "${RED}Error: No se encontró el archivo $file en $dir${NC}"
            fi
        else
            echo "Usando archivo por defecto (docker-compose.yml)"
            docker-compose up -d
        fi
        cd ..
    else
        echo -e "${RED}Error: El directorio $dir no existe.${NC}"
    fi
}

# Ejecutar en Laravel (usa docker-compose.yml)
ejecutar_docker "sirpef_laravel" "docker-compose.yml"

# Ejecutar en Vue (usa docker-compose.dev.yml)
ejecutar_docker "sirpef_vue" "docker-compose.dev.yml"

echo -e "\n${GREEN}Proceso terminado.${NC}"
