#!/bin/bash

# Ruta del Virtual Environment
VIRTUALENV_PATH="/home/innovaol/virtualenv/girapp/3.9"
APP_PATH="/home/innovaol/girapp"
LOGS_PATH="$APP_PATH/logs"

echo "🚀 Iniciando despliegue de la aplicación..."

# Activar el entorno virtual
echo "🟢 Activando entorno virtual..."
source $VIRTUALENV_PATH/bin/activate

# Mostrar versiones
echo "📌 Verificando versiones..."
pip --version
python -m django --version

# Aplicar migraciones
echo "🛠️ Aplicando migraciones..."
python $APP_PATH/manage.py migrate

# Recolectar archivos estáticos
echo "📦 Recolectando archivos estáticos..."
python $APP_PATH/manage.py collectstatic --noinput

# Reiniciar Passenger
echo "🔄 Reiniciando Passenger..."
touch $APP_PATH/tmp/restart.txt

# Mostrar últimos logs
echo "📜 Últimos registros del servidor:"
tail -n 20 $LOGS_PATH/passenger.log
tail -n 20 $LOGS_PATH/debug.log

echo "✅ Despliegue completado correctamente."
