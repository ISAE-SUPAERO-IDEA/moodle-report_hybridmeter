#/usr/bin/env python3.7

PROJECT = 'xapi'

from setuptools import setup, find_packages

try:
    long_description = open('README.rst', 'rt').read()
except IOError:
    long_description = 'ça existe pas'

setup(
    name=PROJECT,
    version='1.0',

    description='CLI pour récupération de traces xApi sur un LRS à un BD',
    long_description=long_description,

    author='Gauthier Hautecoeur',
    author_email='hautecoeur.gauthier4@gmail.com',

    classifiers=[
        'Development Status :: 2-Pre-Alpha',
        'Environment :: Console',
        'Programming Language :: Python :: 3 :: Only'
    ],

    scripts=[],

    provides=[],
    install_requires=['cliff', 'elasticsearch', 'requests', 'cachetools'],

    namespace_packages=[],
    packages=find_packages(),
    include_package_data=True,

    entry_points={
        'console_scripts': [
            'xapi = xapi.main:main'
        ],
        'xapi': [
            'config = xapi.command_cli.config:Config',
            'statements = xapi.command_cli.statements:Statements',
            'info = xapi.command_cli.info:Info',
            'enrich = xapi.command_cli.enrichment:Enrich',
            'advanced_enrich = xapi.command_cli.advanced_enrich:AdvancedEnrich'
        ]
    },

    zip_safe=False
)
