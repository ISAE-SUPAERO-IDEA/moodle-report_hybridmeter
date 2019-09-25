#!/usr/bin/env python

from distutils.core import setup

setup(name='xDashboards',
      version='1.0',
      description='ISAE-SUPAERO xDashboards',
      author='Bruno Ilponse',
      author_email='bruno.ilponse@isae-supaero.fr',
      url='https://www.isae-supaero.fr',
      packages=['xdashboards'],
      install_requires=[
          'django',
          'elasticsearch'
        ],
      )
